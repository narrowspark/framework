<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Closure;
use Narrowspark\Arr\Arr;
use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Routing\Events\RouteMatchedEvent;
use Viserio\Component\Routing\Resolver\MiddlewareName;
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;
use Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer;
use Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder;
use Viserio\Component\Routing\TreeGenerator\RouteTreeCompiler;

abstract class AbstractRouteDispatcher
{
    use ContainerAwareTrait;
    use EventsAwareTrait;
    use MiddlewareAwareTrait;

    /**
     * The route collection instance.
     *
     * @var \Viserio\Component\Routing\Route\Collection
     */
    protected $routes;

    /**
     * The globally available parameter patterns.
     *
     * @var string[]
     */
    protected $globalParameterConditions = [];

    /**
     * The currently dispatched route instance.
     *
     * @var \Viserio\Component\Contracts\Routing\Route
     */
    protected $current;

    /**
     * Path to the cached router file.
     *
     * @var string
     */
    protected $path;

    /**
     * Flag for refresh the cache file on every call.
     *
     * @var bool
     */
    protected $refreshCache = false;

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
     * Add a list of middlewares.
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
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
     */
    public function setMiddlewarePriorities(array $middlewarePriorities): void
    {
        $this->middlewarePriority = $middlewarePriorities;
    }

    /**
     * Get a list of middleware priorities.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function getMiddlewarePriorities(): array
    {
        return $this->middlewarePriority;
    }

    /**
     * Get all with and without middlewares.
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * {@inheritdoc}
     */
    public function setCachePath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * {@inheritdoc}
     */
    public function getCachePath(): string
    {
        return $this->path;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshCache(bool $refreshCache): void
    {
        $this->refreshCache = $refreshCache;
    }

    /**
     * Match and dispatch a route matching the given http method and
     * uri, retruning an execution chain.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @throws \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     * @throws \Narrowspark\HttpStatus\Exception\NotFoundException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function dispatchToRoute(ServerRequestInterface $request): ResponseInterface
    {
        $router = $this->generateRouterFile();
        $match  = $router(
            $request->getMethod(),
           '/' . ltrim($request->getUri()->getPath(), '/')
        );
        $requestPath = ltrim($request->getUri()->getPath(), '/');

        if ($match[0] === RouterContract::FOUND) {
            return $this->handleFound($match[1], $match[2], $request);
        }

        if ($match[0] === RouterContract::HTTP_METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException(sprintf(
                '405 Method [%s] Not Allowed: For requested route [/%s]',
                implode(',', $match[1]),
                $requestPath
            ));
        }

        throw new NotFoundException(sprintf(
            '404 Not Found: Requested route [/%s]',
            $requestPath
        ));
    }

    /**
     * Handle dispatching of a found route.
     *
     * @param string                                   $identifier
     * @param array                                    $segments
     * @param \Psr\Http\Message\ServerRequestInterface $serverRequest
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleFound(
        string $identifier,
        array $segments,
        ServerRequestInterface $serverRequest
    ): ResponseInterface {
        $route = $this->routes->match($identifier);

        foreach ($this->globalParameterConditions as $key => $value) {
            $route->setParameter($key, $value);
        }

        foreach ($segments as $key => $value) {
            $route->setParameter($key, rawurldecode($value));
        }

        // Add route to the request's attributes in case a middleware or handler needs access to the route
        $serverRequest = $serverRequest->withAttribute('_route', $route);

        $this->current = $route;

        if ($this->events !== null) {
            $this->getEventManager()->trigger(
                new RouteMatchedEvent(
                    $this,
                    ['route' => $route, 'server_request' => $serverRequest]
                )
            );
        }

        return $this->runRouteWithinStack($route, $serverRequest);
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
            $this->createCacheFolder($this->path);

            $routerCompiler = new RouteTreeCompiler(new RouteTreeBuilder(), new RouteTreeOptimizer());

            file_put_contents($this->path, $routerCompiler->compile($this->routes->getRoutes()), LOCK_EX);
        }

        return require $this->path;
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
        $middlewares = $this->getRouteMiddlewares($route);

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
    protected function getRouteMiddlewares(RouteContract $route): array
    {
        $middlewares      = [];
        $routeMiddlewares = $route->gatherMiddleware();

        Arr::map($routeMiddlewares['middlewares'], function ($name) use (&$middlewares) {
            $middlewares[] = MiddlewareName::resolve($name, $this->middlewares, $this->middlewareGroups);
        });

        if (count($routeMiddlewares['without_middlewares']) !== 0) {
            $withoutMiddlewares = [];

            Arr::map($routeMiddlewares['without_middlewares'], function ($name) use (&$withoutMiddlewares) {
                $withoutMiddlewares[] = MiddlewareName::resolve($name, $this->middlewares, $this->middlewareGroups);
            });

            $middlewares = array_diff($middlewares, $withoutMiddlewares);
        }

        return (new SortedMiddleware(
            $this->middlewarePriority,
            array_values(Arr::flatten($middlewares))
        ))->getAll();
    }

    /**
     * Make a nested path, creating directories down the path recursion.
     *
     * @param string $path
     *
     * @return bool
     */
    private function createCacheFolder(string $path): bool
    {
        $dir = pathinfo($path, PATHINFO_DIRNAME);

        if (is_dir($dir)) {
            return true;
        }

        if ($this->createCacheFolder($dir)) {
            if (mkdir($dir)) {
                chmod($dir, 0777);

                return true;
            }
        }

        return false;
    }
}
