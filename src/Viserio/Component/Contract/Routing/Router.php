<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Router
{
    public const METHOD_HEAD    = 'HEAD';
    public const METHOD_GET     = 'GET';
    public const METHOD_POST    = 'POST';
    public const METHOD_PUT     = 'PUT';
    public const METHOD_PATCH   = 'PATCH';
    public const METHOD_DELETE  = 'DELETE';
    public const METHOD_PURGE   = 'PURGE';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_TRACE   = 'TRACE';
    public const METHOD_CONNECT = 'CONNECT';
    public const METHOD_LINK    = 'LINK';
    public const METHOD_UNLINK  = 'UNLINK';

    /**
     * Register a new GET route with the router.
     *
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function get(string $uri, $action = null): Route;

    /**
     * Register a new POST route with the router.
     *
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function post(string $uri, $action = null): Route;

    /**
     * Register a new PUT route with the router.
     *
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function put(string $uri, $action = null): Route;

    /**
     * Register a new PATCH route with the router.
     *
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function patch(string $uri, $action = null): Route;

    /**
     * Register a new HEAD route with the router.
     *
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function head(string $uri, $action = null): Route;

    /**
     * Register a new DELETE route with the router.
     *
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function delete(string $uri, $action = null): Route;

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function options(string $uri, $action = null): Route;

    /**
     * Register a new route responding to all verbs.
     *
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
     */
    public function any(string $uri, $action = null): Route;

    /**
     * Register a new route with the given verbs.
     *
     * @param array|string               $methods
     * @param string                     $uri
     * @param null|array|\Closure|string $action
     *
     * @return \Viserio\Component\Contract\Routing\Route
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
    public function addParameter(string $parameterName, string $expression): Router;

    /**
     * Removes the global expression associated with the supplied parameter name.
     *
     * @param string $name
     *
     * @return void
     */
    public function removeParameter(string $name): void;

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
     *
     * @return void
     */
    public function group(array $attributes, $routes): void;

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
     * Get the currently dispatched route instance.
     *
     * @return null|\Viserio\Component\Contract\Routing\Route
     */
    public function getCurrentRoute(): ?Route;

    /**
     * Get the router dispatcher.
     *
     * @return \Viserio\Component\Contract\Routing\Dispatcher
     */
    public function getDispatcher(): Dispatcher;

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
     * @return \Viserio\Component\Contract\Routing\RouteCollection
     */
    public function getRoutes(): RouteCollection;

    /**
     * Register an array of resource controllers.
     *
     * @param array $resources
     *
     * @return void
     */
    public function resources(array $resources): void;

    /**
     * Route a resource to a controller.
     *
     * @param string $name
     * @param string $controller
     * @param array  $options
     *
     * @return \Viserio\Component\Contract\Routing\PendingResourceRegistration
     */
    public function resource(string $name, string $controller, array $options = []): PendingResourceRegistration;
}
