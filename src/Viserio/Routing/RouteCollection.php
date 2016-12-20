<?php
declare(strict_types=1);
namespace Viserio\Routing;

use RuntimeException;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\RouteCollection as RouteCollectionContract;

class RouteCollection implements RouteCollectionContract
{
    /**
     * An flattened array of all of the routes.
     *
     * @var array
     */
    protected $allRoutes = [];

    /**
     * A look-up table of routes by their names.
     *
     * @var array
     */
    protected $nameList = [];

    /**
     * A look-up table of routes by controller action.
     *
     * @var array
     */
    protected $actionList = [];

    /**
     * {@inheritdoc}
     */
    public function add(RouteContract $route): RouteContract
    {
        $domainAndUri = $route->getDomain() . $route->getUri();

        $this->allRoutes[implode($route->getMethods(), '|') . $domainAndUri] = $route;

        $this->addLookups($route);

        return $route;
    }

    /**
     * {@inheritdoc}
     */
    public function match(string $identifier): RouteContract
    {
        if (isset($this->allRoutes[$identifier])) {
            return $this->allRoutes[$identifier];
        }

        throw new RuntimeException('Route not found, looks like your route cache is stale.');
    }

    /**
     * {@inheritdoc}
     */
    public function hasNamedRoute(string $name): bool
    {
        return ! is_null($this->getByName($name));
    }

    /**
     * {@inheritdoc}
     */
    public function getByName(string $name)
    {
        return isset($this->nameList[$name]) ? $this->nameList[$name] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getByAction(string $action)
    {
        return isset($this->actionList[$action]) ? $this->actionList[$action] : null;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getRoutes(): array
    {
        return array_values($this->allRoutes);
    }

    /**
     * Add the route to any look-up tables if necessary.
     *
     * @param \Viserio\Contracts\Routing\Route $route
     */
    protected function addLookups(RouteContract $route)
    {
        // If the route has a name, we will add it to the name look-up table so that we
        // will quickly be able to find any route associate with a name and not have
        // to iterate through every route every time we need to perform a look-up.
        $action = $route->getAction();

        if (isset($action['as'])) {
            $this->nameList[$action['as']] = $route;
        }

        // When the route is routing to a controller we will also store the action that
        // is used by the route. This will let us reverse route to controllers while
        // processing a request and easily generate URLs to the given controllers.
        if (isset($action['controller'])) {
            $this->actionList[trim($action['controller'], '\\')] = $route;
        }
    }
}
