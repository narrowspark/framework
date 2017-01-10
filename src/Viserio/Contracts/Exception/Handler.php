<?php
declare(strict_types=1);
namespace Viserio\Contracts\Exception;

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
     * Add the transformed instance.
     *
     * @param Transformer $transformer
     *
     * @return $this
     */
    public function addTransformer(Transformer $transformer): Handler;

    /**
     * Get the transformer exceptions.
     *
     * @return array
     */
    public function getTransformers(): array;

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
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @return void|null
     */
    public function report(Throwable $exception);

    /**
     * Determine if the exception shouldn't be reported.
     *
     * @param \Throwable $exception
     *
     * @return $this
     */
    public function addShouldntReport(Throwable $exception): Handler;

    /**
     * Register the exception / Error handlers for the application.
     */
    public function register();

    /**
     * Unregister the PHP error handler.
     */
    public function unregister();

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param \Throwable|\Exception $exception
     *
     * @return void|string
     */
    public function handleException($exception);

    /**
     * Handle the PHP shutdown event.
     */
    public function handleShutdown();

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
