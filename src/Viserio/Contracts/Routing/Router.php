<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

use Closure;

interface Router
{
    /**
     * Register a new GET route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function get(string $uri, $action = null): Route;

    /**
     * Register a new POST route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function post(string $uri, $action = null): Route;

    /**
     * Register a new PUT route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function put(string $uri, $action = null): Route;

    /**
     * Register a new PATCH route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function patch(string $uri, $action = null): Route;

    /**
     * Register a new DELETE route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function delete(string $uri, $action = null): Route;

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function options(string $uri, $action = null): Route;

    /**
     * Register a new route responding to all verbs.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function any(string $uri, $action = null): Route;

    /**
     * Register a new route with the given verbs.
     *
     * @param array|string               $methods
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function match($methods, $uri, $action = null): Route;

    /**
     * Get the parent group.
     *
     * @return \Viserio\Contracts\Routing\RouteGroup
     */
    public function getGroup(): RouteGroup;

    /**
     * Create a route group with shared attributes.
     *
     * @param array    $attributes
     * @param \Closure $callback
     *
     * @return \Viserio\Contracts\Routing\Router
     */
    public function group(array $attributes, Closure $callback): Router;
}
