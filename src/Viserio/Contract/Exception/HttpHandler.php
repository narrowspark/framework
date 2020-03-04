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

namespace Viserio\Contract\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface HttpHandler extends Handler
{
    /**
     * Register the exception / Error handlers for the application.
     */
    public function register(): void;

    /**
     * Unregister the PHP error handler.
     */
    public function unregister(): void;

    /**
     * Add the displayer instance.
     *
     * @param \Viserio\Contract\Exception\Displayer $displayer
     *
     * @return self
     */
    public function addDisplayer(Displayer $displayer, int $priority = 0): HttpHandler;

    /**
     * Get the displayer instance.
     */
    public function getDisplayers(): array;

    /**
     * Add the filter instance.
     *
     * @param \Viserio\Contract\Exception\Filter $filter
     *
     * @return self
     */
    public function addFilter(Filter $filter, int $priority = 0): HttpHandler;

    /**
     * Get the filter exceptions.
     */
    public function getFilters(): array;

    /**
     * Render an exception into an HTTP response.
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface;
}
