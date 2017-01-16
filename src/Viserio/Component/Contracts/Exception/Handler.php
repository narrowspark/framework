<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Exception;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface Handler
{
    /**
     * Add the displayer instance.
     *
     * @param Displayer $displayer
     *
     * @return $this
     */
    public function addDisplayer(Displayer $displayer): Handler;

    /**
     * Get the displayer instance.
     *
     * @return array
     */
    public function getDisplayers(): array;

    /**
     * Add the filter instance.
     *
     * @param Filter $filter
     *
     * @return $this
     */
    public function addFilter(Filter $filter): Handler;

    /**
     * Get the filter exceptions.
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Register the exception / Error handlers for the application.
     */
    public function register();

    /**
     * Unregister the PHP error handler.
     */
    public function unregister();

    /**
     * Render an exception into a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface;
}
