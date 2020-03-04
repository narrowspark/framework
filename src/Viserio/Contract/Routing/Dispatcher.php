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
     */
    public function setCachePath(string $path): void;

    /**
     * Get the cache path for the compiled routes.
     */
    public function getCachePath(): string;

    /**
     * Refresh cache file on development.
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
     * @throws \Narrowspark\HttpStatus\Exception\MethodNotAllowedException
     * @throws \Narrowspark\HttpStatus\Exception\NotFoundException
     */
    public function handle(RouteCollectionContract $routes, ServerRequestInterface $request): ResponseInterface;
}
