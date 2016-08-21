<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Viserio\Contracts\Routing\Dispatcher;
use Viserio\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Routing\Generator\ChildrenNodeCollection;
use Viserio\Routing\Generator\MatchedRouteDataMap;
use Viserio\Routing\Generator\RouteTreeBuilder;
use Viserio\Routing\Generator\RouteTreeOptimizer;

class TreeRouteCompiler
{
    /**
     * RouteTreeBuilder instance.
     *
     * @var \Viserio\Routing\Generator\RouteTreeBuilder
     */
    protected $treeBuilder;

    /**
     * RouteTreeOptimizer instance.
     *
     * @var \Viserio\Routing\Generator\RouteTreeOptimizer
     */
    protected $treeOptimizer;

    /**
     * Create a new tree route compailer instance.
     *
     * @param \Viserio\Routing\Generator\RouteTreeBuilder   $treeBuilder
     * @param \Viserio\Routing\Generator\RouteTreeOptimizer $treeOptimizer
     */
    public function __construct(RouteTreeBuilder $treeBuilder, RouteTreeOptimizer $treeOptimizer)
    {
        $this->treeBuilder = $treeBuilder;
        $this->treeOptimizer = $treeOptimizer;
    }

    /**
     * Complie all added routes to a router handler.
     *
     * @param \Viserio\Contracts\Routing\RouteCollection $routes
     *
     * @return string
     */
    public function compile(RouteCollectionContract $routes): string
    {
        $routeTree = $this->treeOptimizer->optimize(
            $this->treeBuilder->build($routes->toArray())
        );

        $code = $this->phpBuilder();
        $code->indent = 1;

        $this->compileRouteTree($code, $routeTree);

        $rootRouteCode = $this->phpBuilder();
        $rootRouteCode->indent = 2;

        if ($routeTree[0] !== null && ! $routeTree[0]->isEmpty()) {
            $this->compiledRouteHttpMethodMatch($rootRouteCode, $routeTree[0], []);
        } else {
            $this->compileNotFound($rootRouteCode);
        }

        return $this->createRouterClassTemplate(substr($rootRouteCode->code, 0, -strlen(PHP_EOL)), $code->code);
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
     * [compileRouteTree description]
     *
     * @param object $code
     * @param array  $routeTree
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
     * [compileSegmentNodes description]
     *
     * @param object                 $code
     * @param ChildrenNodeCollection $nodes
     * @param array                  $segmentVariables
     * @param array                  $parameters
     */
    protected function compileSegmentNodes($code, ChildrenNodeCollection $nodes, array $segmentVariables, array $parameters = [])
    {
        $originalParameters = $parameters;

        foreach ($nodes->getChildren() as $node) {
            $parameters = $originalParameters;
            $segmentMatchers = $node->getMatchers();
            $conditions = [];
            $currentParameter = empty($parameters) ? 0 : max(array_keys($parameters)) + 1;
            $count = $currentParameter;

            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $conditions[] = $matcher->getConditionExpression($segmentVariables[$segmentDepth], $count++);
            }

            $code->appendLine('if (' . implode(' && ', $conditions) . ') {');

            ++$code->indent;

            $count = $currentParameter;

            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $matchedParameters = $matcher->getMatchedParameterExpressions($segmentVariables[$segmentDepth], $count++);

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

        if ($routeDataMap->hasDefaultRouteData()) {
            $this->compileFoundRoute($code, $routeDataMap->getDefaultRouteData(), $parameters);
        } else {
            foreach ($routeDataMap->getAllowedHttpMethods() as $method) {
                $code->appendLine('$allowedHttpMethods[] = ' . VarExporter::export($method) . ';');
            }

            $code->appendLine('break;');
        }

        --$code->indent;
        --$code->indent;

        $code->appendLine('}');
    }

    /**
     * [compileNotFound description]
     *
     * @param object $code
     */
    protected function compileNotFound($code)
    {
        $code->appendLine('return [' . VarExporter::export(Dispatcher::NOT_FOUND) . '];');
    }

    /**
     * [compileDisallowedHttpMethod
     *
     * @param object $code
     * @param array  $allowedMethod
     */
    protected function compileDisallowedHttpMethod($code, array $allowedMethod)
    {
        $code->appendLine('return [' . VarExporter::export(Dispatcher::HTTP_METHOD_NOT_ALLOWED) . ', ' . VarExporter::export($allowedMethod) . '];');
    }

    /**
     * [compileDisallowedHttpMethodOrNotFound
     *
     * @param object $code
     */
    protected function compileDisallowedHttpMethodOrNotFound($code)
    {
        $code->appendLine('return ' .
            'isset($allowedHttpMethods) '
            . '? '
            . '['
            . VarExporter::export(Dispatcher::HTTP_METHOD_NOT_ALLOWED)
            . ', $allowedHttpMethods] '
            . ': '
            . '['
            . VarExporter::export(Dispatcher::NOT_FOUND)
            . '];');
    }

    /**
     * [compileFoundRoute description]
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

        if (strlen($parameters) > 2) {
            $parameters = substr($parameters, 0, -2);
        }

        $parameters .= ']';

        $code->appendLine('return ['
            . VarExporter::export(Dispatcher::FOUND)
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
     * @return object
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
             * The current indentation level of the code
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
