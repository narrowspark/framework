<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Bus;

use Closure;

interface Dispatcher
{
    /**
     * Set the method to call on the command.
     */
    public function via(string $method): self;

    /**
     * Get the handler instance for the given command.
     */
    public function resolveHandler($command): object;

    /**
     * Get the handler class for the given command.
     */
    public function getHandlerClass($command): string;

    /**
     * Get the handler method for the given command.
     */
    public function getHandlerMethod($command): string;

    /**
     * Register command to handler mappings.
     */
    public function maps(array $commands): void;

    /**
     * Register a fallback mapper callback.
     */
    public function mapUsing(Closure $mapper): void;

    /**
     * Dispatch a command to its appropriate handler.
     */
    public function dispatch($command, ?Closure $afterResolving = null);

    /**
     * Set the pipes through which commands should be piped before dispatching.
     */
    public function pipeThrough(array $pipes): self;
}
