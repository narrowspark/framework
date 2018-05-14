<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Dispatcher;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\Exception\RuntimeException;
use Viserio\Component\Contract\Routing\Route as RouteContract;
use Viserio\Component\Contract\Routing\RouteCollection as RouteCollectionContract;
use Viserio\Component\Routing\Event\RouteMatchedEvent;
use Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer;
use Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder;
use Viserio\Component\Routing\TreeGenerator\RouteTreeCompiler;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class SimpleDispatcher implements DispatcherContract
{
    use EventManagerAwareTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * The currently dispatched route instance.
     *
     * @var \Viserio\Component\Contract\Routing\Route
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
        $cacheFile = $this->getCachePath();
        $dir       = \pathinfo($cacheFile, \PATHINFO_DIRNAME);

        if ($this->refreshCache === true || ! \file_exists($cacheFile)) {
            self::generateDirectory($dir);

            $this->generateRouterFile($routes);
        }

        $router = require $cacheFile;

        $match = $router(\mb_strtoupper($request->getMethod()), $this->prepareUriPath($request->getUri()->getPath()));

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
     * @param \Viserio\Component\Contract\Routing\RouteCollection $routes
     * @param \Psr\Http\Message\ServerRequestInterface            $request
     * @param string                                              $identifier
     * @param array                                               $segments
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
            $route->addParameter($key, \rawurldecode($value));
        }

        // Add route to the request's attributes in case a middleware or handler needs access to the route.
        $request = $request->withAttribute('_route', $route);

        $this->current = $route;

        if ($this->eventManager !== null) {
            $this->eventManager->trigger(new RouteMatchedEvent($this, $route, $request));
        }

        return $this->runRoute($route, $request);
    }

    /**
     * Run the given route.
     *
     * @param \Viserio\Component\Contract\Routing\Route $route
     * @param \Psr\Http\Message\ServerRequestInterface  $request
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
     * @param \Viserio\Component\Contract\Routing\RouteCollection $routes
     *
     * @return void
     */
    protected function generateRouterFile(RouteCollectionContract $routes): void
    {
        $routerCompiler = new RouteTreeCompiler(new RouteTreeBuilder(), new RouteTreeOptimizer());
        $closure        = $routerCompiler->compile($routes->getRoutes());

        \file_put_contents($this->path, $closure, \LOCK_EX);
    }

    /**
     * Generate a cache directory.
     *
     * @param string $directory
     *
     * @throws \Viserio\Component\Contract\Routing\Exception\RuntimeException
     *
     * @return void
     */
    private static function generateDirectory(string $directory): void
    {
        if ((! \is_dir($directory) && ! @\mkdir($directory, 0777, true)) || ! \is_writable($directory)) {
            throw new RuntimeException(\sprintf(
                'Route cache directory [%s] cannot be created or is write protected.',
                $directory
            ));
        }
    }
}
