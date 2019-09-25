<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Routing;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contract\Routing\RouteCollection as RouteCollectionContract;

interface Dispatcher
{
    /**
     * Match number for a not found route.
     *
     * @var int
     */
    public const NOT_FOUND = 0;

    /**
     * Match number for a found route.
     *
     * @var int
     */
    public const FOUND = 1;

    /**
     * Match number for a not allowed http method.
     *
     * @var int
     */
    public const HTTP_METHOD_NOT_ALLOWED = 2;

    /**
     * Set the cache path for compiled routes.
     *
     * @param string $path
     *
     * @return void
     */
    public function setCachePath(string $path): void;

    /**
     * Get the cache path for the compiled routes.
     *
     * @return string
     */
    public function getCachePath(): string;

    /**
     * Refresh cache file on development.
     *
     * @param bool $refreshCache
     *
     * @return void
     */
    public function refreshCache(bool $refreshCache): void;

    /**
     * Get the currently dispatched route instance.
     *
     * @return null|\Viserio\Contract\Routing\Route
     */
    public function getCurrentRoute(): ?Route;

    /**
     * Match and dispatch a route matching the given http method and uri.
     *
     * @param \Viserio\Contract\Routing\RouteCollection $routes
     * @param \Psr\Http\Message\ServerRequestInterface  $request
     *
     * @throws \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     * @throws \Narrowspark\HttpStatus\Exception\NotFoundException
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function handle(RouteCollectionContract $routes, ServerRequestInterface $request): ResponseInterface;
}
