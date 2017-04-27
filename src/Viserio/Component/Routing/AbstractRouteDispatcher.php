<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

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
use Viserio\Component\Routing\Traits\MiddlewareAwareTrait;
use Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer;
use Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder;
use Viserio\Component\Routing\TreeGenerator\RouteTreeCompiler;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

abstract class AbstractRouteDispatcher
{
    use ContainerAwareTrait;
    use EventsAwareTrait;
    use MiddlewareAwareTrait;
    use NormalizePathAndDirectorySeparatorTrait;

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
     * {@inheritdoc}
     */
    public function setCachePath(string $path): void
    {
        $this->path = self::normalizeDirectorySeparator($path);
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
     * uri, returning an execution chain.
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
        if (! file_exists($this->path) || $this->refreshCache === true) {
            $this->createCacheFolder($this->path);
            $this->generateRouterFile();
        }

        $router = require $this->path;
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
                    $route,
                    $serverRequest
                )
            );
        }

        return $this->runRouteWithinStack($route, $serverRequest);
    }

    /**
     * Generates a router file with all routes.
     *
     * @return void
     */
    protected function generateRouterFile(): void
    {
        $routerCompiler = new RouteTreeCompiler(new RouteTreeBuilder(), new RouteTreeOptimizer());
        $closure        = $routerCompiler->compile($this->routes->getRoutes());

        file_put_contents($this->path, $closure, LOCK_EX);
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
     * Applies the callback to the elements of the given arrays
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

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }
}
