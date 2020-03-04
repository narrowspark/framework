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

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Router
{
    public const METHOD_HEAD = 'HEAD';
    public const METHOD_GET = 'GET';
    public const METHOD_POST = 'POST';
    public const METHOD_PUT = 'PUT';
    public const METHOD_PATCH = 'PATCH';
    public const METHOD_DELETE = 'DELETE';
    public const METHOD_PURGE = 'PURGE';
    public const METHOD_OPTIONS = 'OPTIONS';
    public const METHOD_TRACE = 'TRACE';
    public const METHOD_CONNECT = 'CONNECT';
    public const METHOD_LINK = 'LINK';
    public const METHOD_UNLINK = 'UNLINK';

    /**
     * Register a new GET route with the router.
     *
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function get(string $uri, $action = null): Route;

    /**
     * Register a new POST route with the router.
     *
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function post(string $uri, $action = null): Route;

    /**
     * Register a new PUT route with the router.
     *
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function put(string $uri, $action = null): Route;

    /**
     * Register a new PATCH route with the router.
     *
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function patch(string $uri, $action = null): Route;

    /**
     * Register a new HEAD route with the router.
     *
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function head(string $uri, $action = null): Route;

    /**
     * Register a new DELETE route with the router.
     *
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function delete(string $uri, $action = null): Route;

    /**
     * Register a new OPTIONS route with the router.
     *
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function options(string $uri, $action = null): Route;

    /**
     * Register a new route responding to all verbs.
     *
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function any(string $uri, $action = null): Route;

    /**
     * Register a new route with the given verbs.
     *
     * @param array|string              $methods
     * @param null|array|Closure|string $action
     *
     * @return \Viserio\Contract\Routing\Route
     */
    public function match($methods, string $uri, $action = null): Route;

    /**
     * Set a global where pattern on all routes.
     */
    public function pattern(string $key, string $pattern): self;

    /**
     * Set a group of global where patterns on all routes.
     */
    public function patterns(array $patterns): self;

    /**
     * Get the global "where" patterns.
     */
    public function getPatterns(): array;

    /**
     * Defines the supplied parameter name to be globally associated with the expression.
     */
    public function addParameter(string $parameterName, string $expression): self;

    /**
     * Removes the global expression associated with the supplied parameter name.
     */
    public function removeParameter(string $name): void;

    /**
     * Get all global parameters for all routes.
     */
    public function getParameters(): array;

    /**
     * Create a route group with shared attributes.
     *
     * @param Closure|string $routes
     */
    public function group(array $attributes, $routes): void;

    /**
     * Merge the given array with the last group stack.
     */
    public function mergeWithLastGroup(array $new): array;

    /**
     * Get the suffix from the last group on the stack.
     */
    public function getLastGroupSuffix(): string;

    /**
     * Get the prefix from the last group on the stack.
     */
    public function getLastGroupPrefix(): string;

    /**
     * Determine if the router currently has a group stack.
     */
    public function hasGroupStack(): bool;

    /**
     * Get the current group stack for the router.
     */
    public function getGroupStack(): array;

    /**
     * Get the currently dispatched route instance.
     *
     * @return null|\Viserio\Contract\Routing\Route
     */
    public function getCurrentRoute(): ?Route;

    /**
     * Get the router dispatcher.
     *
     * @return \Viserio\Contract\Routing\Dispatcher
     */
    public function getDispatcher(): Dispatcher;

    /**
     * Dispatch router for HTTP request.
     */
    public function dispatch(ServerRequestInterface $request): ResponseInterface;

    /**
     * Get the underlying route collection.
     *
     * @return \Viserio\Contract\Routing\RouteCollection
     */
    public function getRoutes(): RouteCollection;

    /**
     * Register an array of resource controllers.
     */
    public function resources(array $resources): void;

    /**
     * Route a resource to a controller.
     *
     * @return \Viserio\Contract\Routing\PendingResourceRegistration
     */
    public function resource(string $name, string $controller, array $options = []): PendingResourceRegistration;
}
