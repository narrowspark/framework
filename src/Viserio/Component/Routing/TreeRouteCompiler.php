<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Routing\Generator\ChildrenNodeCollection;
use Viserio\Component\Routing\Generator\MatchedRouteDataMap;
use Viserio\Component\Routing\Generator\RouteTreeBuilder;
use Viserio\Component\Routing\Generator\RouteTreeOptimizer;

class TreeRouteCompiler
{
    /**
     * RouteTreeBuilder instance.
     *
     * @var \Viserio\Component\Routing\Generator\RouteTreeBuilder
     */
    protected $treeBuilder;

    /**
     * RouteTreeOptimizer instance.
     *
     * @var \Viserio\Component\Routing\Generator\RouteTreeOptimizer
     */
    protected $treeOptimizer;

    /**
     * Create a new tree route compailer instance.
     *
     * @param \Viserio\Component\Routing\Generator\RouteTreeBuilder   $treeBuilder
     * @param \Viserio\Component\Routing\Generator\RouteTreeOptimizer $treeOptimizer
     */
    public function __construct(RouteTreeBuilder $treeBuilder, RouteTreeOptimizer $treeOptimizer)
    {
        $this->treeBuilder   = $treeBuilder;
        $this->treeOptimizer = $treeOptimizer;
    }

    /**
     * Complie all added routes to a router handler.
     *
     * @param array $routes
     *
     * @return string
     */
    public function compile(array $routes): string
    {
        $routeTree = $this->treeOptimizer->optimize(
            $this->treeBuilder->build($routes)
        );

        $code         = $this->phpBuilder();
        $code->indent = 1;

        $this->compileRouteTree($code, $routeTree);

        $rootRouteCode         = $this->phpBuilder();
        $rootRouteCode->indent = 2;

        $this->compileNotFound($rootRouteCode);

        return $this->createRouterClassTemplate(mb_substr($rootRouteCode->code, 0, -mb_strlen(PHP_EOL)), $code->code);
    }

    /**
     * Creating a template for the router class.
     *
     * @param string $rootRoute
     * @param string $body
     *
     * @return string
     */
    protected function createRouterClassTemplate(string $rootRoute, string $body): string
    {
        $template = <<<'PHP'
<?php
return function ($method, $uri) {
    if($uri === '') {
{root_route}
    } elseif ($uri[0] !== '/') {
        throw new \RuntimeException("Cannot match route: non-empty uri must be prefixed with '/', '{$uri}' given");
    }
    $segments = explode('/', substr($uri, 1));
{body}
};
PHP;

        return strtr($template, ['{root_route}' => $rootRoute, '{body}' => $body]);
    }

    /**
     * Compile the counter for the segments check.
     *
     * @param anonymous//src/Viserio/Routing/TreeRouteCompiler.php$0 $code
     * @param array $routeTree
     * @param mixed $code
     */
    protected function compileRouteTree($code, array $routeTree)
    {
        $code->appendLine('switch (count($segments)) {');

        ++$code->indent;

        foreach ($routeTree[1] as $segmentDepth => $nodes) {
            $code->appendLine('case ' . VarExporter::export($segmentDepth) . ':');

            ++$code->indent;

            $segmentVariables = [];

            for ($i = 0; $i < $segmentDepth; ++$i) {
                $segmentVariables[$i] = '$s' . $i;
            }

            $code->appendLine('list(' . implode(', ', $segmentVariables) . ') = $segments;');

            $this->compileSegmentNodes($code, $nodes, $segmentVariables);
            $this->compileDisallowedHttpMethodOrNotFound($code);

            $code->appendLine('break;');

            --$code->indent;

            $code->appendLine();
        }

        $code->appendLine('default:');

        ++$code->indent;

        $this->compileNotFound($code);

        --$code->indent;
        --$code->indent;

        $code->append('}');
    }

    /**
     * Comple the segemtns nodes to if statements.
     *
     * @param object                 $code
     * @param ChildrenNodeCollection $nodes
     * @param array                  $segmentVariables
     * @param array                  $parameters
     */
    protected function compileSegmentNodes(
        $code,
        ChildrenNodeCollection $nodes,
        array $segmentVariables,
        array $parameters = []
    ) {
        $originalParameters = $parameters;

        foreach ($nodes->getChildren() as $node) {
            $parameters       = $originalParameters;
            $segmentMatchers  = $node->getMatchers();
            $conditions       = [];
            $currentParameter = empty($parameters) ? 0 : max(array_keys($parameters)) + 1;
            $count            = $currentParameter;

            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $conditions[] = $matcher->getConditionExpression($segmentVariables[$segmentDepth], $count++);
            }

            $code->appendLine('if (' . implode(' && ', $conditions) . ') {');

            ++$code->indent;

            $count = $currentParameter;

            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $matchedParameters = $matcher->getMatchedParameterExpressions(
                    $segmentVariables[$segmentDepth],
                    $count++
                );

                foreach ($matchedParameters as $parameterKey => $matchedParameter) {
                    $parameters[$parameterKey] = $matchedParameter;
                }
            }

            $contents = $node->getContents();

            if ($contents instanceof MatchedRouteDataMap) {
                $this->compiledRouteHttpMethodMatch($code, $contents, $parameters);
            } else {
                $this->compileSegmentNodes($code, $contents, $segmentVariables, $parameters);
            }

            --$code->indent;

            $code->appendLine('}');
        }
    }

    /**
     * Compile the route http method match switch.
     *
     * @param object              $code
     * @param MatchedRouteDataMap $routeDataMap
     * @param array               $parameters
     */
    protected function compiledRouteHttpMethodMatch($code, MatchedRouteDataMap $routeDataMap, array $parameters)
    {
        $code->appendLine('switch ($method) {');

        ++$code->indent;

        foreach ($routeDataMap->getHttpMethodRouteDataMap() as $item) {
            list($httpMethods, $routeData) = $item;

            foreach ($httpMethods as $httpMethod) {
                $code->appendLine('case ' . VarExporter::export($httpMethod) . ':');
            }

            ++$code->indent;

            $this->compileFoundRoute($code, $routeData, $parameters);

            --$code->indent;
        }

        $code->appendLine('default:');

        ++$code->indent;

        foreach ($routeDataMap->allowedHttpMethods() as $method) {
            $code->appendLine('$allowedHttpMethods[] = ' . VarExporter::export($method) . ';');
        }

        $code->appendLine('break;');

        --$code->indent;
        --$code->indent;

        $code->appendLine('}');
    }

    /**
     * Compile the return data.
     *
     * @param object $code
     */
    protected function compileNotFound($code)
    {
        $code->appendLine('return [' . VarExporter::export(RouterContract::NOT_FOUND) . '];');
    }

    /**
     * Compile disallowed http method or not found data check.
     *
     * @param object $code
     */
    protected function compileDisallowedHttpMethodOrNotFound($code)
    {
        $code->appendLine(
            'return ' .
            'isset($allowedHttpMethods) '
            . '? '
            . '['
            . VarExporter::export(RouterContract::HTTP_METHOD_NOT_ALLOWED)
            . ', $allowedHttpMethods] '
            . ': '
            . '['
            . VarExporter::export(RouterContract::NOT_FOUND)
            . '];'
        );
    }

    /**
     * Compile the found route data.
     *
     * @param object $code
     * @param array  $foundRoute
     * @param array  $parameterExpressions
     */
    protected function compileFoundRoute($code, array $foundRoute, array $parameterExpressions)
    {
        $parameters = '[';

        foreach ($foundRoute[0] as $index => $parameterName) {
            $parameters .= VarExporter::export($parameterName) . ' => ' . $parameterExpressions[$index] . ', ';
        }

        if (mb_strlen($parameters) > 2) {
            $parameters = mb_substr($parameters, 0, -2);
        }

        $parameters .= ']';

        $code->appendLine(
            'return ['
            . VarExporter::export(RouterContract::FOUND)
            . ', '
            . VarExporter::export($foundRoute[1])
            . ', '
            . $parameters
            . '];'
        );
    }

    /**
     * The php code builder class.
     *
     * @return anonymous//src/Viserio/Routing/TreeRouteCompiler.php@return object
     */
    private function phpBuilder()
    {
        return new class() {
            /**
             * The php code.
             *
             * @var string
             */
            public $code = '';

            /**
             * The current indentation level of the code.
             *
             * @var int
             */
            public $indent = '';

            /**
             * Appends the supplied code to the builder.
             *
             * @param string $code
             */
            public function append(string $code)
            {
                $indent = str_repeat(' ', 4 * $this->indent);

                $this->code .= $indent . str_replace(PHP_EOL, PHP_EOL . $indent, $code);
            }

            /**
             * Appends the supplied code and a new line to the builder.
             *
             * @param string $code
             */
            public function appendLine(string $code = '')
            {
                $this->append($code);
                $this->code .= PHP_EOL;
            }
        };
    }
}
