<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Routing;

use Countable;

interface RouteCollection extends Countable
{
    /**
     * Add a Route instance to the collection.
     *
     * @param \Viserio\Component\Contract\Routing\Route $route
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function add(Route $route): Route;

    /**
     * Find the first route matching a given identifier.
     *
     * @param string $identifier
     *
     * @throws \Viserio\Component\Contract\Routing\Exception\RuntimeException
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function match(string $identifier): Route;

    /**
     * Determine if the route collection contains a given named route.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasNamedRoute(string $name): bool;

    /**
     * Get a route instance by its name.
     *
     * @param string $name
     *
     * @return null|\Viserio\Component\Contract\Routing\Route
     */
    public function getByName(string $name): ?Route;

    /**
     * Get a route instance by its controller action.
     *
     * @param string $action
     *
     * @return null|\Viserio\Component\Contract\Routing\Route
     */
    public function getByAction(string $action): ?Route;

    /**
     * Get all of the routes in the collection.
     *
     * @return array
     */
    public function getRoutes(): array;
}
