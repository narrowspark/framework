<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Bus;

use Closure;

interface QueueingDispatcher
{
    /**
     * Dispatch a command to its appropriate handler.
     *
     * @param mixed         $command
     * @param null|\Closure $afterResolving
     *
     * @return mixed
     */
    public function dispatch($command, Closure $afterResolving = null);

    /**
     * Dispatch a command to its appropriate handler behind a queue.
     *
     * @param mixed $command
     *
     * @throws \RuntimeException
     *
     * @return mixed
     */
    public function dispatchToQueue($command);
}
