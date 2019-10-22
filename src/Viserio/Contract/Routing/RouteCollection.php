<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
     * @param string $identifier
     *
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException
     *
     * @return \Viserio\Contract\Routing\Route
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
     * @return null|\Viserio\Contract\Routing\Route
     */
    public function getByName(string $name): ?Route;

    /**
     * Get a route instance by its controller action.
     *
     * @param string $action
     *
     * @return null|\Viserio\Contract\Routing\Route
     */
    public function getByAction(string $action): ?Route;

    /**
     * Get all of the routes in the collection.
     *
     * @return array
     */
    public function getRoutes(): array;
}
