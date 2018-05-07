<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Dispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Routing\MiddlewareAware as MiddlewareAwareContract;
use Viserio\Component\Contract\Routing\Route as RouteContract;
use Viserio\Component\Routing\MiddlewareNameResolver;
use Viserio\Component\Routing\Pipeline;
use Viserio\Component\Routing\SortedMiddleware;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;

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
     * @param \Viserio\Component\Contract\Routing\Route $route
     * @param \Psr\Http\Message\ServerRequestInterface  $request
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
            ->then(function ($request) use ($route) {
                return $route->run($request);
            });
    }

    /**
     * Gather the middleware for the given route.
     *
     * @param \Viserio\Component\Contract\Routing\Route $route
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
     *
     * @return array
     */
    protected static function flatten(array $array): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            if (\is_array($value)) {
                $flattened = \array_merge($flattened, static::flatten($value));
            } else {
                $flattened[] = $value;
            }
        }

        return $flattened;
    }
}
