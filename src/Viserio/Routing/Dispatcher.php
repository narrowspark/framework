<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Routing\Dispatcher as DispatcherContract;
use Viserio\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Routing\Generator\RouteTreeBuilder;
use Viserio\Routing\Generator\RouteTreeOptimizer;
use Viserio\Routing\Middlewares\FoundMiddleware;
use Viserio\Routing\Middlewares\InternalServerErrorMiddleware;
use Viserio\Routing\Middlewares\NotAllowedMiddleware;
use Viserio\Routing\Middlewares\NotFoundMiddleware;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Routing\Route as RouteContract;
use Psr\Http\Message\ResponseInterface;
use Interop\Container\ContainerInterface;

class Dispatcher implements DispatcherContract
{
    use EventsAwareTrait;
    use ContainerAwareTrait;

    /**
     * Patch to cache file.
     *
     * @var string
     */
    protected $path;

    /**
     * The route collection instance.
     *
     * @var \Viserio\Contracts\Routing\RouteCollection
     */
    protected $routes;

    /**
     * The currently dispatched route instance.
     *
     * @var \Viserio\Contracts\Routing\Route
     */
    protected $current;

    /**
     * Flag for refresh the cache file on every call.
     *
     * @var bool
     */
    protected $refreshCache = false;

    /**
     * The globally available parameter patterns.
     *
     * @var string[]
     */
    protected $globalParameterConditions = [];

    /**
     * The priority-sorted list of middleware.
     *
     * Forces the listed middleware to always be in the given order.
     *
     * @var array
     */
    protected $priorityMiddlewareMap = [];

    /**
     * Create a new Router instance.
     *
     * @param string                                     $path
     * @param \Interop\Container\ContainerInterface      $container
     * @param \Viserio\Contracts\Routing\RouteCollection $routes
     * @param bool                                       $refreshCache
     * @param array                                      $globalParameterConditions
     * @param array                                      $priorityMiddlewareMap
     */
    public function __construct(
        string $path,
        ContainerInterface $container,
        RouteCollectionContract $routes,
        bool $refreshCache,
        array $globalParameterConditions,
        array $priorityMiddlewareMap
    ) {
        $this->path = $path;
        $this->container = $container;
        $this->routes = $routes;
        $this->refreshCache = $refreshCache;
        $this->globalParameterConditions = $globalParameterConditions;
        $this->priorityMiddlewareMap = $priorityMiddlewareMap;
    }

    /**
     * Match and dispatch a route matching the given http method and
     * uri, retruning an execution chain.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $router = $this->generateRouterFile();
        $match = $router(
            $request->getMethod(),
           '/' . ltrim($request->getUri()->getPath(), '/')
        );

        switch ($match[0]) {
            case DispatcherContract::NOT_FOUND:
                return $this->handleNotFound();
            case DispatcherContract::HTTP_METHOD_NOT_ALLOWED:
                return $this->handleMethodNotAllowed($match[1]);
            case DispatcherContract::FOUND:
                return $this->handleFound($match[1], $match[2], $request);
            default:
                return $this->handleInternalServerError();
        }
    }

    /**
     * Get the currently dispatched route instance.
     *
     * @return \Viserio\Contracts\Routing\Route|null
     */
    public function getCurrentRoute()
    {
        return $this->current;
    }

    /**
     * Handles a internal server error.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleInternalServerError(): MiddlewareDispatcher
    {
        return $this->middlewareDispatcher->withMiddleware(new InternalServerErrorMiddleware());
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
        $route = $this->findRoute($request);

        if ($this->events !== null) {
            $this->getEventsDispatcher()->trigger('route.matched', [$route, $request]);
        }

        return $this->runRouteWithinStack($route, $request);
    }

    /**
     * Handle a not found route.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleNotFound(): ResponseInterface
    {
        return new NotFoundMiddleware();
    }

    /**
     * Handles a not allowed route.
     *
     * @param array $allowed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleMethodNotAllowed(array $allowed): ResponseInterface
    {
        return new NotAllowedMiddleware($allowed);
    }

    /**
     * Find the route matching a given request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Contracts\Routing\Route
     */
    protected function findRoute(ServerRequestInterface $request): RouteContract
    {
        $router = $this->generateRouterFile();
        $match = $router(
            $request->getMethod(),
           '/' . ltrim($request->getUri()->getPath(), '/')
        );

        $route = $this->routes->match($match[1]);

        if ($this->events !== null && $match[0] === self::FOUND) {
            $this->getEventsDispatcher()->trigger('route.matched', [$route, $request]);
        }

        foreach ($this->globalParameterConditions as $key => $value) {
            $route->setParameter($key, $value);
        }

        foreach ($match[2] as $key => $value) {
            $route->setParameter($key, rawurldecode($value));
        }

        return $this->current = $route;
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
     * @return mixed
     */
    protected function runRouteWithinStack(RouteContract $route, ServerRequestInterface $request)
    {
        $middleware = $this->getRouteMiddlewares($route);

        return (new Pipeline)
            ->setContainer($this->container)
            ->send($request)
            ->through($middleware)
            ->then(function ($request) use ($route) {
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
        $middleware = [];

        return $this->doSortMiddleware($this->priorityMiddlewareMap, $middleware);
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
            if (! is_string($middleware)) {
                continue;
            }

            $stripped = head(explode(':', $middleware));

            if (in_array($stripped, $priorityMap)) {
                $priorityIndex = array_search($stripped, $priorityMap);

                // This middleware is in the priority map. If we have encountered another middleware
                // that was also in the priority map and was at a lower priority than the current
                // middleware, we will move this middleware to be above the previous encounter.
                if (isset($lastPriorityIndex) && $priorityIndex < $lastPriorityIndex) {
                    return $this->sortMiddleware(
                        $priorityMap, array_values(
                            $this->doMoveMiddleware($middlewares, $index, $lastIndex)
                        )
                    );

                // This middleware is in the priority map; but, this is the first middleware we have
                // encountered from the map thus far. We'll save its current index plus its index
                // from the priority map so we can compare against them on the next iterations.
                } else {
                    $lastIndex = $index;
                    $lastPriorityIndex = $priorityIndex;
                }
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
}
