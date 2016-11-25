<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Narrowspark\Arr\Arr;
use Narrowspark\HttpStatus\Exception\InternalServerErrorException;
use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Routing\Route as RouteContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Routing\Generator\RouteTreeBuilder;
use Viserio\Routing\Generator\RouteTreeOptimizer;
use Viserio\Routing\Traits\MiddlewareAwareTrait;

abstract class AbstractRouteDispatcher
{
    use ContainerAwareTrait;
    use EventsAwareTrait;
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
     * Register a group of middleware.
     *
     * @param string $name
     * @param array  $middleware
     *
     * @return $this
     */
    public function middlewareGroup(string $name, array $middleware)
    {
        $this->middlewareGroups[$name] = $middleware;

        return $this;
    }

    /**
     * Set a list of middleware priorities.
     *
     * @param array $middlewarePriorities
     *
     * @return $this
     */
    public function setMiddlewarePriorities(array $middlewarePriorities)
    {
        $this->middlewarePriority = $middlewarePriorities;

        return $this;
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
     * Match and dispatch a route matching the given http method and
     * uri, retruning an execution chain.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     * @throws \Narrowspark\HttpStatus\Exception\NotFoundException
     * @throws \Narrowspark\HttpStatus\Exception\InternalServerErrorException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function dispatchToRoute(ServerRequestInterface $request): ResponseInterface
    {
        $router = $this->generateRouterFile();
        $match = $router(
            $request->getMethod(),
           '/' . ltrim($request->getUri()->getPath(), '/')
        );
        $requestPath = ltrim($request->getUri()->getPath(), '/');

        switch ($match[0]) {
            case RouterContract::FOUND:
                return $this->handleFound($match[1], $match[2], $request);
            case RouterContract::HTTP_METHOD_NOT_ALLOWED:
                throw new MethodNotAllowedException(sprintf(
                    '405 Method [%s] Not Allowed: For requested route [/%s]',
                    implode(',', $match[1]),
                    $requestPath
                ));
            case RouterContract::NOT_FOUND:
                throw new NotFoundException(sprintf(
                    '404 Not Found: Requested route [/%s]',
                    $requestPath
                ));
            default:
                throw new InternalServerErrorException();
        }
    }

    /**
     * Handle dispatching of a found route.
     *
     * @param string                                   $identifier
     * @param array                                    $segments
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleFound(
        string $identifier,
        array $segments,
        ServerRequestInterface $request
    ): ResponseInterface {
        $route = $this->routes->match($identifier);

        foreach ($this->globalParameterConditions as $key => $value) {
            $route->setParameter($key, $value);
        }

        foreach ($segments as $key => $value) {
            $route->setParameter($key, rawurldecode($value));
        }

        $this->current = $route;

        if ($this->events !== null) {
            $this->getEventsDispatcher()->trigger('route.matched', [$route, $request]);
        }

        return $this->runRouteWithinStack($route, $request);
    }

    /**
     * Generates a router file with all routes.
     *
     * @return \Closure
     */
    protected function generateRouterFile(): Closure
    {
        if ($this->refreshCache && file_exists($this->path)) {
            @unlink($this->path);
        }

        if (! file_exists($this->path)) {
            $routerCompiler = new TreeRouteCompiler(new RouteTreeBuilder(), new RouteTreeOptimizer());

            file_put_contents($this->path, $routerCompiler->compile($this->routes->getRoutes()), LOCK_EX);
        }

        return require $this->path;
    }

    /**
     * Run the given route within a Stack "onion" instance.
     *
     * @param \Viserio\Contracts\Routing\Route         $route
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function runRouteWithinStack(RouteContract $route, ServerRequestInterface $request): ResponseInterface
    {
        $middlewares = $this->getRouteMiddlewares($route);

        return (new Pipeline())
            ->setContainer($this->getContainer())
            ->send($request)
            ->through($middlewares)
            ->then(function ($request) use ($route) {
                // Add route to the request's attributes in case a middleware or handler needs access to the route
                $request = $request->withAttribute('route', $route);

                return $route->run($request);
            });
    }

    /**
     * Gather the middleware for the given route.
     *
     * @param \Viserio\Contracts\Routing\Route $route
     *
     * @return array
     */
    protected function getRouteMiddlewares(Route $route): array
    {
        $middlewares = [];
        $routeMiddlewares = $route->gatherMiddleware();

        $middleware = Arr::map($routeMiddlewares['middlewares'], function ($name) use (&$middlewares) {
            $middlewares[] = $this->resolveMiddlewareClassName($name);
        });

        if (count($routeMiddlewares['without_middlewares']) !== 0) {
            $withoutMiddlewares = [];

            $middleware = Arr::map($routeMiddlewares['without_middlewares'], function ($name) use (&$withoutMiddlewares) {
                $withoutMiddlewares[] = $this->resolveMiddlewareClassName($name);
            });

            $middlewares = array_diff($middlewares, $withoutMiddlewares);
        }

        return $this->doSortMiddleware($this->middlewarePriority, $middlewares);
    }

    /**
     * Sort the middlewares by the given priority map.
     *
     * Each call to this method makes one discrete middleware movement if necessary.
     *
     * @param array $priorityMap
     * @param array $middlewares
     *
     * @return array
     */
    protected function doSortMiddleware(array $priorityMap, array $middlewares): array
    {
        $lastIndex = 0;

        foreach ($middlewares as $index => $middleware) {
            if (in_array($middleware, $priorityMap)) {
                $priorityIndex = array_search($middleware, $priorityMap);

                // This middleware is in the priority map. If we have encountered another middleware
                // that was also in the priority map and was at a lower priority than the current
                // middleware, we will move this middleware to be above the previous encounter.
                if (isset($lastPriorityIndex) && $priorityIndex < $lastPriorityIndex) {
                    return $this->doSortMiddleware(
                        $priorityMap,
                        array_values(
                            $this->doMoveMiddleware($middlewares, $index, $lastIndex)
                        )
                    );

                // This middleware is in the priority map; but, this is the first middleware we have
                // encountered from the map thus far. We'll save its current index plus its index
                // from the priority map so we can compare against them on the next iterations.
                }
                $lastIndex = $index;
                $lastPriorityIndex = $priorityIndex;
            }
        }

        return array_values(array_unique($middlewares, SORT_REGULAR));
    }

    /**
     * Splice a middleware into a new position and remove the old entry.
     *
     * @param array $middlewares
     * @param int   $from
     * @param int   $to
     *
     * @return array
     */
    protected function doMoveMiddleware(array $middlewares, int $from, int $to): array
    {
        array_splice($middlewares, $to, 0, $middlewares[$from]);
        unset($middlewares[$from + 1]);

        return $middlewares;
    }

    /**
     * Resolve the middleware name to a class name(s) preserving passed parameters.
     *
     * @param string $name
     *
     * @return string|array
     */
    protected function resolveMiddlewareClassName(string $name)
    {
        $map = $this->middlewares;

        if (isset($this->middlewareGroups[$name])) {
            return $this->parseMiddlewareGroup($name);
        }

        return $map[$name] ?? $name;
    }

    /**
     * Parse the middleware group and format it for usage.
     *
     * @param string $name
     *
     * @return array
     */
    protected function parseMiddlewareGroup(string $name): array
    {
        $results = [];

        foreach ($this->middlewareGroups[$name] as $middleware) {
            // If the middleware is another middleware group we will pull in the group and
            // merge its middleware into the results. This allows groups to conveniently
            // reference other groups without needing to repeat all their middlewares.
            if (isset($this->middlewareGroups[$middleware])) {
                $results = array_merge(
                    $results,
                    $this->parseMiddlewareGroup($middleware)
                );

                continue;
            }

            // If this middleware is actually a route middleware, we will extract the full
            // class name out of the middleware list now. Then we'll add the parameters
            // back onto this class' name so the pipeline will properly extract them.
            if (isset($this->middleware[$middleware])) {
                $middleware = $this->middleware[$middleware];
            }

            $results[] = $middleware;
        }

        return $results;
    }
}
