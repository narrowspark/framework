<?php
namespace Viserio\Contracts\Exception;

use Throwable;

interface Handler
{
    /**
     * Add the displayer instance.
     *
     * @param Displayer $displayer
     *
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
     */
    public function addShouldntReport(Throwable $exception): Handler;

    /**
     * Register the exception / Error handlers for the application.
     *
     * @return void
     */
    public function register();

    /**
     * Unregister the PHP error handler.
     *
     * @return void
     */
    public function unregister();

    /**
     * Convert errors into ErrorException objects.
     *
     * This method catches PHP errors and converts them into ErrorException objects;
     * these ErrorException objects are then thrown and caught by Viserio's
     * built-in or custom error handlers.
     *
     * @param int    $level   The numeric type of the Error
     * @param string $message The error message
     * @param string $file    The absolute path to the affected file
     * @param int    $line    The line number of the error in the affected file
     * @param null   $context
     *
     * @return void
     *
     * @throws \ErrorException
     */
    public function handleError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0,
        $context = null
    );

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function handleException(Throwable $exception);

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown();
}
