<?php
declare(strict_types=1);
namespace Viserio\Routing;

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
     * @param array $routes
     *
     * @return string
     */
    public function compile(array $routes): string
    {
    }
}
