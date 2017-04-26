<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Router
{
    /**
     * Match number for a not found route.
     *
     * @var int
     */
    public const NOT_FOUND = 0;

    /**
     * Match number for a found route.
     *
     * @var int
     */
    public const FOUND = 1;

    /**
     * Match number for a not allowed http method.
     *
     * @var int
     */
    public const HTTP_METHOD_NOT_ALLOWED = 2;

    /**
     * Register a new GET route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function get(string $uri, $action = null): Route;

    /**
     * Register a new POST route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function post(string $uri, $action = null): Route;

    /**
     * Register a new PUT route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function put(string $uri, $action = null): Route;

    /**
     * Register a new PATCH route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function patch(string $uri, $action = null): Route;

    /**
     * Register a new HEAD route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function head(string $uri, $action = null): Route;

    /**
     * Register a new DELETE route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function delete(string $uri, $action = null): Route;

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function options(string $uri, $action = null): Route;

    /**
     * Register a new route responding to all verbs.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function any(string $uri, $action = null): Route;

    /**
     * Register a new route with the given verbs.
     *
     * @param array|string               $methods
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Component\Contracts\Routing\Route
     */
    public function match($methods, string $uri, $action = null): Route;

    /**
     * Set a global where pattern on all routes.
     *
     * @param string $key
     * @param string $pattern
     *
     * @return $this
     */
    public function pattern(string $key, string $pattern): Router;

    /**
     * Set a group of global where patterns on all routes.
     *
     * @param array $patterns
     *
     * @return $this
     */
    public function patterns(array $patterns): Router;

    /**
     * Get the global "where" patterns.
     *
     * @return array
     */
    public function getPatterns(): array;

    /**
     * Defines the supplied parameter name to be globally associated with the expression.
     *
     * @param string $parameterName
     * @param string $expression
     *
     * @return $this
     */
    public function setParameter(string $parameterName, string $expression): Router;

    /**
     * Removes the global expression associated with the supplied parameter name.
     *
     * @param string $name
     */
    public function removeParameter(string $name);

    /**
     * Get all global parameters for all routes.
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Create a route group with shared attributes.
     *
     * @param array           $attributes
     * @param \Closure|string $routes
     */
    public function group(array $attributes, $routes);

    /**
     * Merge the given array with the last group stack.
     *
     * @param array $new
     *
     * @return array
     */
    public function mergeWithLastGroup(array $new): array;

    /**
     * Get the suffix from the last group on the stack.
     *
     * @return string
     */
    public function getLastGroupSuffix(): string;

    /**
     * Get the prefix from the last group on the stack.
     *
     * @return string
     */
    public function getLastGroupPrefix(): string;

    /**
     * Determine if the router currently has a group stack.
     *
     * @return bool
     */
    public function hasGroupStack(): bool;

    /**
     * Get the current group stack for the router.
     *
     * @return array
     */
    public function getGroupStack(): array;

    /**
     * Get all with and without middlewares.
     *
     * @return array
     */
    public function getMiddlewares(): array;

    /**
     * Register a group of middleware.
     *
     * @param string $name
     * @param array  $middleware
     *
     * @return $this
     */
    public function setMiddlewareGroup(string $name, array $middleware);

    /**
     * Set a list of middleware priorities.
     *
     * @param array $middlewarePriorities
     *
     * @return $this
     */
    public function setMiddlewarePriorities(array $middlewarePriorities);

    /**
     * Get the currently dispatched route instance.
     *
     * @return \Viserio\Component\Contracts\Routing\Route|null
     */
    public function getCurrentRoute();

    /**
     * Dispatch router for HTTP request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;

    /**
     * Get the underlying route collection.
     *
     * @return \Viserio\Component\Contracts\Routing\RouteCollection
     */
    public function getRoutes(): RouteCollection;

    /**
     * Set the cache path for compiled routes.
     *
     * @param string $path
     *
     * @return void
     */
    public function setCachePath(string $path): void;

    /**
     * Get the cache path for the compiled routes.
     *
     * @return string
     */
    public function getCachePath(): string;

    /**
     * Refresh cache file on development.
     *
     * @param bool $refreshCache
     *
     * @return void
     */
    public function refreshCache(bool $refreshCache): void;
}
