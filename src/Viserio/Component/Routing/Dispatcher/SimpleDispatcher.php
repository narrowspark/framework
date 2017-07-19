<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Dispatcher;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Component\Contracts\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contracts\Routing\Route as RouteContract;
use Viserio\Component\Contracts\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Routing\Event\RouteMatchedEvent;
use Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer;
use Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder;
use Viserio\Component\Routing\TreeGenerator\RouteTreeCompiler;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class SimpleDispatcher implements DispatcherContract
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
     * Set the cache path for compiled routes.
     *
     * @param string $path
     *
     * @return void
     */
    public function setCachePath(string $path): void
    {
        $this->path = self::normalizeDirectorySeparator($path);
    }

    /**
     * Get the cache path for the compiled routes.
     *
     * @return string
     */
    public function getCachePath(): string
    {
        return $this->path;
    }

    /**
     * Refresh cache file on development.
     *
     * @param bool $refreshCache
     *
     * @return void
     */
    public function refreshCache(bool $refreshCache): void
    {
        $this->refreshCache = $refreshCache;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentRoute(): ?RouteContract
    {
        return $this->current;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(RouteCollectionContract $routes, ServerRequestInterface $request): ResponseInterface
    {
        if (! \file_exists($this->path) || $this->refreshCache === true) {
            static::createCacheFolder($this->path);

            $this->generateRouterFile($routes);
        }

        $router = require $this->path;

        $match = $router($request->getMethod(), $this->prepareUriPath($request->getUri()->getPath()));

        if ($match[0] === self::FOUND) {
            return $this->handleFound($routes, $request, $match[1], $match[2]);
        }

        $requestPath = '/' . \ltrim($request->getUri()->getPath(), '/');

        if ($match[0] === self::HTTP_METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException(\sprintf(
                '405 Method [%s] Not Allowed: For requested route [%s].',
                \implode(',', $match[1]),
                $requestPath
            ));
        }

        throw new NotFoundException(\sprintf(
            '404 Not Found: Requested route [%s].',
            $requestPath
        ));
    }

    /**
     * Handle dispatching of a found route.
     *
     * @param \Viserio\Component\Contracts\Routing\RouteCollection $routes
     * @param \Psr\Http\Message\ServerRequestInterface             $request
     * @param string                                               $identifier
     * @param array                                                $segments
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleFound(
        RouteCollectionContract $routes,
        ServerRequestInterface $request,
        string $identifier,
        array $segments
    ): ResponseInterface {
        $route = $routes->match($identifier);

        foreach ($segments as $key => $value) {
            $route->setParameter($key, \rawurldecode($value));
        }

        // Add route to the request's attributes in case a middleware or handler needs access to the route.
        $request = $request->withAttribute('_route', $route);

        $this->current = $route;

        if ($this->events !== null) {
            $this->getEventManager()->trigger(new RouteMatchedEvent($this, $route, $request));
        }

        return $this->runRoute($route, $request);
    }

    /**
     * Run the given route.
     *
     * @param \Viserio\Component\Contracts\Routing\Route $route
     * @param \Psr\Http\Message\ServerRequestInterface   $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function runRoute(RouteContract $route, ServerRequestInterface $request): ResponseInterface
    {
        return $route->run($request);
    }

    /**
     * Prepare the request uri path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function prepareUriPath(string $path): string
    {
        $path = '/' . \ltrim($path, '/');

        if (\mb_strlen($path) !== 1 && \mb_substr($path, -1) === '/') {
            $path = \substr_replace($path, '', -1);
        }

        return $path;
    }

    /**
     * Generates a router file with all routes.
     *
     * @param \Viserio\Component\Contracts\Routing\RouteCollection $routes
     *
     * @return void
     */
    protected function generateRouterFile(RouteCollectionContract $routes): void
    {
        $routerCompiler = new RouteTreeCompiler(new RouteTreeBuilder(), new RouteTreeOptimizer());
        $closure        = $routerCompiler->compile($routes->getRoutes());

        \file_put_contents($this->path, $closure, LOCK_EX);
    }

    /**
     * Make a nested path, creating directories down the path recursion.
     *
     * @param string $path
     *
     * @return bool
     */
    protected static function createCacheFolder(string $path): bool
    {
        $dir = \pathinfo($path, PATHINFO_DIRNAME);

        if (\is_dir($dir)) {
            return true;
        }

        if (static::createCacheFolder($dir)) {
            if (\mkdir($dir)) {
                \chmod($dir, 0777);

                return true;
            }
        }

        // @codeCoverageIgnoreStart
        return false;
        // @codeCoverageIgnoreEnd
    }
}
