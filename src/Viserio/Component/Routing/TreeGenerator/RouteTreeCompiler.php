<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\TreeGenerator;

use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer;

final class RouteTreeCompiler
{
    /**
     * RouteTreeBuilder instance.
     *
     * @var \Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder
     */
    private $treeBuilder;

    /**
     * RouteTreeOptimizer instance.
     *
     * @var \Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer
     */
    private $treeOptimizer;

    /**
     * Create a new tree route compailer instance.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder             $treeBuilder
     * @param \Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer $treeOptimizer
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

        $code         = new PHPCodeCollection();
        $code->indent = 1;

        $this->compileRouteTree($code, $routeTree);

        $rootRouteCode         = new PHPCodeCollection();
        $rootRouteCode->indent = 2;

        $this->compileNotFound($rootRouteCode);

        return $this->createRouterClassTemplate(\mb_substr($rootRouteCode->code, 0, -\mb_strlen(\PHP_EOL)), $code->code);
    }

    /**
     * Creating a template for the router class.
     *
     * @param string $rootRoute
     * @param string $body
     *
     * @return string
     */
    private function createRouterClassTemplate(string $rootRoute, string $body): string
    {
        $template = <<<'PHP'
<?php
return function ($method, $uri) {
    if($uri === '') {
{root_route}
    } elseif ($uri[0] !== '/') {
        throw new \RuntimeException("Cannot match route: non-empty uri must be prefixed with '/', '{$uri}' given");
    }
    $segments = \explode('/', \substr($uri, 1));
{body}
};
PHP;

        return \strtr($template, ['{root_route}' => $rootRoute, '{body}' => $body]);
    }

    /**
     * Compile the counter for the segments check.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\PHPCodeCollection $code
     * @param array                                                      $routeTree
     */
    private function compileRouteTree(PHPCodeCollection $code, array $routeTree): void
    {
        $code->appendLine('switch (count($segments)) {');

        $code->indent++;

        foreach ($routeTree[1] as $segmentDepth => $nodes) {
            $code->appendLine('case ' . $segmentDepth . ':');

            $code->indent++;

            $segmentVariables = [];

            for ($i = 0; $i < $segmentDepth; $i++) {
                $segmentVariables[$i] = '$s' . $i;
            }

            $code->appendLine('[' . \implode(', ', $segmentVariables) . '] = $segments;');

            $this->compileSegmentNodes($code, $nodes, $segmentVariables);
            $this->compileDisallowedHttpMethodOrNotFound($code);

            $code->appendLine('break;');

            $code->indent--;

            $code->appendLine();
        }

        $code->appendLine('default:');

        $code->indent++;

        $this->compileNotFound($code);

        $code->indent--;
        $code->indent--;

        $code->append('}');
    }

    /**
     * Compile the segemtns nodes to if statements.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\PHPCodeCollection      $code
     * @param \Viserio\Component\Routing\TreeGenerator\ChildrenNodeCollection $nodes
     * @param array                                                           $segmentVariables
     * @param array                                                           $parameters
     */
    private function compileSegmentNodes(
        PHPCodeCollection $code,
        ChildrenNodeCollection $nodes,
        array $segmentVariables,
        array $parameters = []
    ): void {
        $originalParameters = $parameters;

        foreach ($nodes->getChildren() as $node) {
            $parameters       = $originalParameters;
            $segmentMatchers  = $node->getMatchers();
            $conditions       = [];
            $currentParameter = \count($parameters) === 0 ? 0 : \max(\array_keys($parameters)) + 1;
            $count            = $currentParameter;

            foreach ($segmentMatchers as $segmentDepth => $matcher) {
                $conditions[] = $matcher->getConditionExpression($segmentVariables[$segmentDepth], $count++);
            }

            $code->appendLine('if (' . \implode(' && ', $conditions) . ') {');

            $code->indent++;

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

            $code->indent--;

            $code->appendLine('}');
        }
    }

    /**
     * Compile the route http method match switch.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\PHPCodeCollection   $code
     * @param \Viserio\Component\Routing\TreeGenerator\MatchedRouteDataMap $routeDataMap
     * @param array                                                        $parameters
     */
    private function compiledRouteHttpMethodMatch(
        PHPCodeCollection $code,
        MatchedRouteDataMap $routeDataMap,
        array $parameters
    ): void {
        $code->appendLine('switch ($method) {');

        $code->indent++;

        foreach ($routeDataMap->getHttpMethodRouteDataMap() as $item) {
            [$httpMethods, $routeData] = $item;

            foreach ($httpMethods as $httpMethod) {
                $code->appendLine('case \'' . $httpMethod . '\':');
            }

            $code->indent++;

            $this->compileFoundRoute($code, $routeData, $parameters);

            $code->indent--;
        }

        $code->appendLine('default:');

        $code->indent++;

        foreach ($routeDataMap->allowedHttpMethods() as $method) {
            $code->appendLine('$allowedHttpMethods[] = \'' . $method . '\';');
        }

        $code->appendLine('break;');

        $code->indent--;
        $code->indent--;

        $code->appendLine('}');
    }

    /**
     * Compile the return data.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\PHPCodeCollection $code
     */
    private function compileNotFound(PHPCodeCollection $code): void
    {
        $code->appendLine('return [' . DispatcherContract::NOT_FOUND . '];');
    }

    /**
     * Compile disallowed http method or not found data check.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\PHPCodeCollection $code
     */
    private function compileDisallowedHttpMethodOrNotFound(PHPCodeCollection $code): void
    {
        $code->appendLine(
            'return ' .
            'isset($allowedHttpMethods) '
            . '? '
            . '['
            . DispatcherContract::HTTP_METHOD_NOT_ALLOWED
            . ', $allowedHttpMethods] '
            . ': '
            . '['
            . DispatcherContract::NOT_FOUND
            . '];'
        );
    }

    /**
     * Compile the found route data.
     *
     * @param \Viserio\Component\Routing\TreeGenerator\PHPCodeCollection $code
     * @param array                                                      $foundRoute
     * @param array                                                      $parameterExpressions
     */
    private function compileFoundRoute(PHPCodeCollection $code, array $foundRoute, array $parameterExpressions): void
    {
        $parameters = '[';

        foreach ($foundRoute[0] as $index => $parameterName) {
            $parameters .= '\'' . $parameterName . '\' => ' . $parameterExpressions[$index] . ', ';
        }

        if (\mb_strlen($parameters) > 2) {
            $parameters = \mb_substr($parameters, 0, -2);
        }

        $parameters .= ']';

        $code->appendLine(
            'return ['
            . DispatcherContract::FOUND
            . ', '
            . '\'' . $foundRoute[1] . '\''
            . ', '
            . $parameters
            . '];'
        );
    }
}
