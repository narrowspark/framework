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
     * The middelware dispatcher instance.
     *
     * @var \Viserio\Middleware\Dispatcher
     */
    protected $middlewareDispatcher;

    /**
     * Flag for development mode.
     *
     * @var bool
     */
    protected $isDevelopMode = true;

    /**
     * Create a new Router instance.
     *
     * @param string                                     $path
     * @param \Viserio\Contracts\Routing\RouteCollection $routes
     * @param \Viserio\Middleware\Dispatcher             $middlewareDispatcher
     * @param bool                                       $isDevelopMode
     */
    public function __construct(
        string $path,
        RouteCollectionContract $routes,
        MiddlewareDispatcher $middlewareDispatcher,
        bool $isDevelopMode
    ) {
        $this->path = $path;
        $this->routes = $routes;
        $this->middlewareDispatcher = $middlewareDispatcher;
        $this->isDevelopMode = $isDevelopMode;
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
                return $this->handleFound($match[1], $match[2]);
            default:
                return $this->handleInternalServerError();
        }
    }

    /**
     * Handle dispatching of a found route.
     *
     * @param string $identifier
     * @param array  $segments
     *
     * @return \Viserio\Middleware\Dispatcher
     */
    protected function handleFound(string $identifier, array $segments): MiddlewareDispatcher
    {
        $route = $this->routes->match($identifier);

        foreach ($segments as $key => $value) {
            $route->setParameter($key, urldecode($value));
        }

        return $this->middlewareDispatcher->withMiddleware(new FoundMiddleware($route));
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
     * Generates a router file with all routes.
     *
     * @return \Closure
     */
    protected function generateRouterFile(): Closure
    {
        if ($this->isDevelopMode && file_exists($this->path)) {
            @unlink($this->path);
        }

        if (! file_exists($this->path)) {
            $routerCompiler = new TreeRouteCompiler(new RouteTreeBuilder(), new RouteTreeOptimizer());

            file_put_contents($this->path, $routerCompiler->compile($this->routes->getRoutes()));
        }

        return require $this->path;
    }
}
