<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Viserio\Contracts\{
    Container\Traits\ContainerAwareTrait,
    Routing\Route as RouteContract
};

class RouteCollection
{
    use ContainerAwareTrait;

   /**
     * An array of the routes keyed by method.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = [];

    /**
      * @var array
      */
     protected $namedRoutes = [];

    /**
     * @var array
     */
    protected $filters = [];

    /**
     * @var \Viserio\Routing\RouteGroup[]
     */
    protected $groups = [];

    /**
     * Add a Route instance to the collection.
     *
     * @param Viserio\Contracts\Routing\Route $route
     *
     * @return Viserio\Contracts\Routing\Route
     */
    public function addRoute(RouteContract $route): RouteContract
    {
        $this->addToCollections($route);

        return $route;
    }

    /**
     * Get all of the routes in the collection.
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return array_values($this->allRoutes);
    }

    /**
     * Add the given route to the arrays of routes.
     *
     * @param Viserio\Contracts\Routing\Route $route
     */
    protected function addToCollections(RouteContract $route)
    {
        $domainAndUri = $route->getDomain() . $route->getUri();

        foreach ($route->getMethods() as $method) {
            $this->routes[$method][$domainAndUri] = $route;
        }

        $this->allRoutes[$method.$domainAndUri] = $route;
    }
}
