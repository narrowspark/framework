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

namespace Viserio\Component\Routing\Dispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Routing\MiddlewareNameResolver;
use Viserio\Component\Routing\Pipeline;
use Viserio\Component\Routing\SortedMiddleware;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;
use Viserio\Contract\Routing\Route as RouteContract;

class MiddlewareBasedDispatcher extends SimpleDispatcher implements MiddlewareAwareContract
{
    use ContainerAwareTrait;
    use MiddlewareAwareTrait;

    /**
     * All of the middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [];

    /**
     * Register middleware groups.
     *
     * @param array $groups
     *
     * @return void
     */
    public function setMiddlewareGroups(array $groups): void
    {
        $this->middlewareGroups = $groups;
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function withoutMiddleware($middleware = null): MiddlewareAwareContract
    {
        // not used!

        return $this;
    }

    /**
     * Register a group of middleware.
     *
     * @param string $name
     * @param array  $middleware
     *
     * @return void
     */
    public function setMiddlewareGroup(string $name, array $middleware): void
    {
        $this->middlewareGroups[$name] = $middleware;
    }

    /**
     * Set a list of middleware priorities.
     *
     * @param array $middlewarePriorities
     *
     * @return void
     */
    public function setMiddlewarePriorities(array $middlewarePriorities): void
    {
        $this->middlewarePriority = $middlewarePriorities;
    }

    /**
     * Get a list of middleware priorities.
     *
     * @return array
     */
    public function getMiddlewarePriorities(): array
    {
        return $this->middlewarePriority;
    }

    /**
     * Get a middleware list.
     *
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    /**
     * Run the given route within a Stack "onion" instance.
     *
     * @param \Viserio\Contract\Routing\Route          $route
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function runRoute(RouteContract $route, ServerRequestInterface $request): ResponseInterface
    {
        $pipeline = new Pipeline();

        if ($this->container !== null) {
            $pipeline->setContainer($this->container);
        }

        return $pipeline->send($request)
            ->through($this->gatherRouteMiddleware($route))
            ->then(static function ($request) use ($route) {
                return $route->run($request);
            });
    }

    /**
     * Gather the middleware for the given route.
     *
     * @param \Viserio\Contract\Routing\Route $route
     *
     * @return array
     */
    protected function gatherRouteMiddleware(RouteContract $route): array
    {
        $middleware = [];

        self::map($route->gatherMiddleware(), function ($nameOrObject) use (&$middleware, $route): void {
            $bypass = $route->gatherDisabledMiddleware();

            if (\is_object($nameOrObject) && ! isset($bypass[\get_class($nameOrObject)])) {
                $middleware[] = $nameOrObject;
            } else {
                $middleware[] = MiddlewareNameResolver::resolve(
                    $nameOrObject,
                    $this->middleware,
                    $this->middlewareGroups,
                    $bypass
                );
            }
        });

        return (new SortedMiddleware(
            $this->middlewarePriority,
            self::flatten($middleware)
        ))->getAll();
    }

    /**
     * Applies the callback to the elements of the given arrays.
     *
     * @param array    $array
     * @param callable $callback
     *
     * @return array
     */
    protected static function map(array $array, callable $callback): array
    {
        $newArray = [];

        foreach ($array as $key => $item) {
            $result = $callback($item, $key);

            $newArray = \array_merge_recursive($array, (array) $result);
        }

        return $newArray;
    }

    /**
     * Convert a multi-dimensional array into a single-dimensional array without keys.
     *
     * @param array $array
     * @param int   $depth
     *
     * @return array
     */
    protected static function flatten(array $array, $depth = \INF): array
    {
        $result = [];

        foreach ($array as $value) {
            if (! \is_array($value)) {
                $result[] = $value;
            } else {
                $values = $depth === 1
                    ? \array_values($value)
                    : static::flatten($value, $depth - 1);

                \array_push($result, ...$values);
            }
        }

        return $result;
    }
}
