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

namespace Viserio\Contract\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface HttpHandler extends Handler
{
    /**
     * Register the exception / Error handlers for the application.
     *
     * @return void
     */
    public function register(): void;

    /**
     * Unregister the PHP error handler.
     *
     * @return void
     */
    public function unregister(): void;

    /**
     * Add the displayer instance.
     *
     * @param \Viserio\Contract\Exception\Displayer $displayer
     * @param int                                   $priority
     *
     * @return self
     */
    public function addDisplayer(Displayer $displayer, int $priority = 0): HttpHandler;

    /**
     * Get the displayer instance.
     *
     * @return array
     */
    public function getDisplayers(): array;

    /**
     * Add the filter instance.
     *
     * @param \Viserio\Contract\Exception\Filter $filter
     * @param int                                $priority
     *
     * @return self
     */
    public function addFilter(Filter $filter, int $priority = 0): HttpHandler;

    /**
     * Get the filter exceptions.
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface;
}
