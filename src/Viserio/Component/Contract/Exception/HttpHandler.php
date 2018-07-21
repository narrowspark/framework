<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Exception;

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
     * @param \Viserio\Component\Contract\Exception\Displayer $displayer
     *
     * @return \Viserio\Component\Contract\Exception\HttpHandler
     */
    public function addDisplayer(Displayer $displayer): HttpHandler;

    /**
     * Get the displayer instance.
     *
     * @return array
     */
    public function getDisplayers(): array;

    /**
     * Add the filter instance.
     *
     * @param \Viserio\Component\Contract\Exception\Filter $filter
     *
     * @return \Viserio\Component\Contract\Exception\HttpHandler
     */
    public function addFilter(Filter $filter): HttpHandler;

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
