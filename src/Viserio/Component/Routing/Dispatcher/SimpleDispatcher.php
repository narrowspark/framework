<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Routing\Dispatcher;

use Narrowspark\HttpStatus\Exception\MethodNotAllowedException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Routing\Event\RouteMatchedEvent;
use Viserio\Component\Routing\TreeGenerator\Optimizer\RouteTreeOptimizer;
use Viserio\Component\Routing\TreeGenerator\RouteTreeBuilder;
use Viserio\Component\Routing\TreeGenerator\RouteTreeCompiler;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Contract\Routing\Exception\RuntimeException;
use Viserio\Contract\Routing\Route as RouteContract;
use Viserio\Contract\Routing\RouteCollection as RouteCollectionContract;

class SimpleDispatcher implements DispatcherContract
{
    use EventManagerAwareTrait;

    /**
     * The currently dispatched route instance.
     *
     * @var \Viserio\Contract\Routing\Route
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
     * Refresh cache file on development.
     */
    public function refreshCache(bool $refreshCache): void
    {
        $this->refreshCache = $refreshCache;
    }

    /**
     * Set the cache path for compiled routes.
     */
    public function setCachePath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Get the cache path for the compiled routes.
     */
    public function getCachePath(): string
    {
        return $this->path;
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
        $dir = \pathinfo($cacheFile, \PATHINFO_DIRNAME);

        if ($this->refreshCache === true || ! \file_exists($cacheFile)) {
            self::generateDirectory($dir);

            $this->generateRouterFile($routes);
        }

        $router = require $cacheFile;

        $match = $router(\strtoupper($request->getMethod()), $this->prepareUriPath($request->getUri()->getPath()));

        if ($match[0] === self::FOUND) {
            return $this->handleFound($routes, $request, $match[1], $match[2]);
        }

        $requestPath = '/' . \ltrim($request->getUri()->getPath(), '/');

        if ($match[0] === self::HTTP_METHOD_NOT_ALLOWED) {
            throw new MethodNotAllowedException(\sprintf('405 Method [%s] Not Allowed: For requested route [%s].', \implode(',', $match[1]), $requestPath));
        }

        throw new NotFoundException(\sprintf('404 Not Found: Requested route [%s].', $requestPath));
    }

    /**
     * Handle dispatching of a found route.
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
     */
    protected function runRoute(RouteContract $route, ServerRequestInterface $request): ResponseInterface
    {
        return $route->run($request);
    }

    /**
     * Prepare the request uri path.
     */
    protected function prepareUriPath(string $path): string
    {
        $path = '/' . \ltrim($path, '/');

        if (\strlen($path) !== 1 && \substr($path, -1) === '/') {
            $path = \substr_replace($path, '', -1);
        }

        return $path;
    }

    /**
     * Generates a router file with all routes.
     */
    protected function generateRouterFile(RouteCollectionContract $routes): void
    {
        $routerCompiler = new RouteTreeCompiler(new RouteTreeBuilder(), new RouteTreeOptimizer());
        $closure = $routerCompiler->compile($routes->getRoutes());

        \file_put_contents($this->path, $closure, \LOCK_EX);
    }

    /**
     * Generate a cache directory.
     *
     * @throws \Viserio\Contract\Routing\Exception\RuntimeException
     */
    private static function generateDirectory(string $directory): void
    {
        if ((! \is_dir($directory) && ! @\mkdir($directory, 0777, true)) || ! \is_writable($directory)) {
            throw new RuntimeException(\sprintf('Route cache directory [%s] cannot be created or is write protected.', $directory));
        }
    }
}
