<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Routing\Dispatcher as DispatcherContract;
use Viserio\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Middleware\Dispatcher as MiddlewareDispatcher;
use Viserio\Routing\Generator\RouteTreeBuilder;
use Viserio\Routing\Generator\RouteTreeOptimizer;
use Viserio\Routing\Middlewares\FoundMiddleware;
use Viserio\Routing\Middlewares\InternalServerErrorMiddleware;
use Viserio\Routing\Middlewares\NotAllowedMiddleware;
use Viserio\Routing\Middlewares\NotFoundMiddleware;

class Dispatcher implements DispatcherContract
{
    use EventsAwareTrait;

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
     * The middelware dispatcher instance.
     *
     * @var \Viserio\Middleware\Dispatcher
     */
    protected $middlewareDispatcher;

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
     * Create a new Router instance.
     *
     * @param string                                     $path
     * @param \Viserio\Contracts\Routing\RouteCollection $routes
     * @param \Viserio\Middleware\Dispatcher             $middlewareDispatcher
     * @param bool                                       $refreshCache
     */
    public function __construct(
        string $path,
        RouteCollectionContract $routes,
        MiddlewareDispatcher $middlewareDispatcher,
        bool $refreshCache,
        array $globalParameterConditions
    ) {
        $this->path = $path;
        $this->routes = $routes;
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->refreshCache = $refreshCache;
        $this->globalParameterConditions = $globalParameterConditions;
    }

    /**
     * Match and dispatch a route matching the given http method and
     * uri, retruning an execution chain.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Middleware\Dispatcher
     */
    public function handle(ServerRequestInterface $request): MiddlewareDispatcher
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
     *
     * @return \Viserio\Middleware\Dispatcher
     */
    public function handleInternalServerError(): MiddlewareDispatcher
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
     * @return \Viserio\Middleware\Dispatcher
     */
    protected function handleFound(
        string $identifier,
        array $segments,
        ServerRequestInterface $request
    ): MiddlewareDispatcher {
        $route = $this->routes->match($identifier);

        foreach ($this->globalParameterConditions as $key => $value) {
            $route->setParameter($key, $value);
        }

        foreach ($segments as $key => $value) {
            $route->setParameter($key, urldecode($value));
        }

        $this->current = $route;

        $this->middlewareDispatcher->withMiddleware(new FoundMiddleware($route));

        $this->addMiddlewares($route->gatherMiddleware());

        if ($this->events !== null) {
            $this->getEventsDispatcher()->trigger('route.matched', [$route, $request]);
        }

        return $this->middlewareDispatcher;
    }

    /**
     * Handle a not found route.
     *
     * @return \Viserio\Middleware\Dispatcher
     */
    protected function handleNotFound(): MiddlewareDispatcher
    {
        return $this->middlewareDispatcher->withMiddleware(new NotFoundMiddleware());
    }

    /**
     * Handles a not allowed route.
     *
     * @param array $allowed
     *
     * @return \Viserio\Middleware\Dispatcher
     */
    protected function handleMethodNotAllowed(array $allowed): MiddlewareDispatcher
    {
        return $this->middlewareDispatcher->withMiddleware(new NotAllowedMiddleware($allowed));
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
     * If route has middlewares add it to the middleware dispatcher.
     *
     * @param array $middlewares
     */
    private function addMiddlewares(array $middlewares)
    {
        if (count($middlewares['with']) !== 0) {
            foreach ($middlewares['with'] as $middleware) {
                $this->middlewareDispatcher->withMiddleware($middleware);
            }
        }

        if (count($middlewares['without']) !== 0) {
            foreach ($middlewares['without'] as $middleware) {
                $this->middlewareDispatcher->withoutMiddleware($middleware);
            }
        }
    }
}
