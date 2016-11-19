<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Router
{
    /**
     * All of the verbs supported by the router.
     *
     * @var array
     */
    const HTTP_METHOD_VARS = ['GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

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
     * Register a new HEAD route with the router.
     *
     * @param string                     $uri
     * @param \Closure|array|string|null $action
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    public function head(string $uri, $action = null): Route;

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
     * Defines the supplied parameter name to be globally associated with the expression.
     *
     * @param string[] $parameterPatternMap
     *
     * @return $this
     */
    public function addParameters(array $parameterPatternMap): Router;

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
     * Merge the given group attributes.
     *
     * @param array $new
     * @param array $old
     *
     * @return array
     */
    public function mergeGroup(array $new, array $old): array;

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
     * Add a middleware to all routes.
     *
     * @param \Interop\Http\Middleware\ServerMiddlewareInterface $middleware
     *
     * @return $this
     */
    public function withMiddleware(ServerMiddlewareInterface $middleware);

    /**
     * Remove a middleware from all routes.
     *
     * @param \Interop\Http\Middleware\ServerMiddlewareInterface $middleware
     *
     * @return $this
     */
    public function withoutMiddleware(ServerMiddlewareInterface $middleware);

    /**
     * Get all with and without middlewares.
     *
     * @return array
     */
    public function getMiddlewares(): array;

    /**
     * Get the currently dispatched route instance.
     *
     * @return \Viserio\Contracts\Routing\Route|null
     */
    public function getCurrentRoute();

    /**
     * Dispatch router for HTTP request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface;

    /**
     * Get the underlying route collection.
     *
     * @return \Viserio\Contracts\Routing\RouteCollection
     */
    public function getRoutes(): RouteCollection;
}
