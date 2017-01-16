<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Queue;

interface Monitor
{
    /**
     * Register a callback to be executed when a job fails after the maximum amount of retries.
     *
     * @param mixed $callback
     */
    public function failing($callback);

    /**
     * Register a callback to be executed when a daemon queue is stopping.
     *
     * @param mixed $callback
     */
    public function stopping($callback);

    /**
     * Register an event listener for the exception occurred job event.
     *
     * @param mixed $callback
     */
    public function exceptionOccurred($callback);
}
