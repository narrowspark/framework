<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Dispatcher;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;
use Viserio\Component\Routing\Pipeline;

class MiddlewareBasedDispatcher extends BasicDispatcher
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
     * Add a middleware list.
     *
     * @param array $middlewares
     *
     * @return void
     */
    public function addMiddlewares(array $middlewares): void
    {
        $this->middlewares = $middlewares;
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
     * Get all with and without middlewares.
     *
     * @return array
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * Run the given route within a Stack "onion" instance.
     *
     * @param \Viserio\Component\Contracts\Routing\Route $route
     * @param \Psr\Http\Message\ServerRequestInterface   $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function runRouteWithinStack(RouteContract $route, ServerRequestInterface $request): ResponseInterface
    {
        $middlewares = $this->gatherRouteMiddleware($route);

        return (new Pipeline())
            ->setContainer($this->getContainer())
            ->send($request)
            ->through($middlewares)
            ->then(function ($request) use ($route) {
                return $route->run($request);
            });
    }

    /**
     * Gather the middleware for the given route.
     *
     * @param \Viserio\Component\Contracts\Routing\Route $route
     *
     * @return array
     */
    protected function gatherRouteMiddleware(RouteContract $route): array
    {
        $middlewares      = [];

        self::map($route->gatherMiddleware(), function ($name) use (&$middlewares, $route) {
            $middlewares[] = MiddlewareNameResolver::resolve(
                $name,
                $this->middlewares,
                $this->middlewareGroups,
                $route->gatherDisabledMiddlewares()
            );
        });

        return (new SortedMiddleware(
            $this->middlewarePriority,
            array_values(self::flatten($middlewares))
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

            $newArray = is_array($result) ?
                array_replace_recursive($array, $result) :
                array_merge_recursive($array, (array) $result);
        }

        return $newArray;
    }

    /**
     * Flatten a nested array to a separated key.
     *
     * @param array  $array
     * @param string $prepend
     *
     * @return array
     */
    protected static function flatten(array $array, string $prepend = ''): array
    {
        $flattened = [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $flattened = array_merge($flattened, static::flatten($value, $prepend . $key));
            } else {
                $flattened[$prepend . $key] = $value;
            }
        }

        return $flattened;
    }
}
