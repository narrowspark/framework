<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Dispatcher;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Routing\Events\RouteMatchedEvent;
use Viserio\Component\Routing\Route\Collection;
use Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer;
use Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder;
use Viserio\Component\Routing\TreeGenerator\RouteTreeCompiler;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class BasicDispatcher
{
    use EventsAwareTrait;
    use NormalizePathAndDirectorySeparatorTrait;

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
     * @param \Viserio\Component\Routing\Route\Collection $routes
     * @param \Psr\Http\Message\ServerRequestInterface    $request
     *
     * @throws \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     * @throws \Narrowspark\HttpStatus\Exception\NotFoundException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function dispatchToRoute(Collection $routes, ServerRequestInterface $request): ResponseInterface
    {
        if (! file_exists($this->path) || $this->refreshCache === true) {
            $this->createCacheFolder($this->path);
            $this->generateRouterFile($routes);
        }

        $router = require $this->path;
        $match  = $router(
            $request->getMethod(),
           '/' . ltrim($request->getUri()->getPath(), '/')
        );

        if ($match[0] === RouterContract::FOUND) {
            return $this->handleFound($routes, $request, $match[1], $match[2]);
        }

        $requestPath = ltrim($request->getUri()->getPath(), '/');

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
     * @param \Viserio\Component\Routing\Route\Collection $routes
     * @param \Psr\Http\Message\ServerRequestInterface    $serverRequest
     * @param string                                      $identifier
     * @param array                                       $segments
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleFound(
        Collection $routes,
        ServerRequestInterface $serverRequest,
        string $identifier,
        array $segments
    ): ResponseInterface {
        $route = $routes->match($identifier);

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
     * @param \Viserio\Component\Routing\Route\Collection $routes
     *
     * @return void
     */
    protected function generateRouterFile(Collection $routes): void
    {
        $routerCompiler = new RouteTreeCompiler(new RouteTreeBuilder(), new RouteTreeOptimizer());
        $closure        = $routerCompiler->compile($routes->getRoutes());

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
        return $route->run($request);
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
