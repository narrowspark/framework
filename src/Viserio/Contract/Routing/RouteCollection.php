<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Routing;

use Countable;

interface RouteCollection extends Countable
{
    /**
     * Add a Route instance to the collection.
     *
     * @param \Viserio\Contract\Routing\Route $route
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function add(Route $route): Route;

    /**
     * Find the first route matching a given identifier.
     *
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function match(string $identifier): Route;

    /**
     * Determine if the route collection contains a given named route.
     */
    public function hasNamedRoute(string $name): bool;

    /**
     * Get a route instance by its name.
     *
     * @return null|\Viserio\Contract\Routing\Route
     */
    public function getByName(string $name): ?Route;

    /**
     * Get a route instance by its controller action.
     *
     * @return null|\Viserio\Contract\Routing\Route
     */
    public function getByAction(string $action): ?Route;

    /**
     * Get all of the routes in the collection.
     */
    public function getRoutes(): array;
}
