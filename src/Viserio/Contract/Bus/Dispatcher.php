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

namespace Viserio\Contract\Bus;

use Closure;

interface Dispatcher
{
    /**
     * Set the method to call on the command.
     *
     * @param string $method
     *
     * @return self
     */
    public function via(string $method): self;

    /**
     * Get the handler instance for the given command.
     *
     * @param mixed $command
     *
     * @return object
     */
    public function resolveHandler($command): object;

    /**
     * Get the handler class for the given command.
     *
     * @param mixed $command
     *
     * @return string
     */
    public function getHandlerClass($command): string;

    /**
     * Get the handler method for the given command.
     *
     * @param mixed $command
     *
     * @return string
     */
    public function getHandlerMethod($command): string;

    /**
     * Register command to handler mappings.
     *
     * @param array $commands
     *
     * @return void
     */
    public function maps(array $commands): void;

    /**
     * Register a fallback mapper callback.
     *
     * @param Closure $mapper
     *
     * @return void
     */
    public function mapUsing(Closure $mapper): void;

    /**
     * Dispatch a command to its appropriate handler.
     *
     * @param mixed        $command
     * @param null|Closure $afterResolving
     *
     * @return mixed
     */
    public function dispatch($command, ?Closure $afterResolving = null);

    /**
     * Set the pipes through which commands should be piped before dispatching.
     *
     * @param array $pipes
     *
     * @return self
     */
    public function pipeThrough(array $pipes): self;
}
