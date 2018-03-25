<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Exception;

use Throwable;

interface ConsoleHandler extends Handler
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
     * Render an exception to the console.
     *
     * @param \Viserio\Component\Contract\Exception\ConsoleOutput $output
     * @param \Throwable                                          $exception
     *
     * @return void
     */
    public function render(ConsoleOutput $output, Throwable $exception): void;
}
