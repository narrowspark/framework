<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Exception;

use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;

interface Handler extends ExceptionHandlerContract
{
    /**
     * Add the displayer instance.
     *
     * @param \Viserio\Component\Contracts\Exception\Displayer $displayer
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
     * @param \Viserio\Component\Contracts\Exception\Filter $filter
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
}
