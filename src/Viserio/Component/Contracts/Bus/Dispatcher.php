<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Bus;

use Closure;

interface Dispatcher
{
    /**
     * Set the method to call on the command.
     *
     * @param string $method
     *
     * @return $this
     */
    public function via(string $method): Dispatcher;

    /**
     * Get the handler instance for the given command.
     *
     * @param mixed $command
     *
     * @return mixed
     */
    public function resolveHandler($command);

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
     * @param \Closure $mapper
     *
     * @return void
     */
    public function mapUsing(Closure $mapper);

    /**
     * Dispatch a command to its appropriate handler.
     *
     * @param mixed         $command
     * @param \Closure|null $afterResolving
     *
     * @return mixed
     */
    public function dispatch($command, Closure $afterResolving = null);

    /**
     * Set the pipes through which commands should be piped before dispatching.
     *
     * @param array $pipes
     *
     * @return $this
     */
    public function pipeThrough(array $pipes): Dispatcher;
}
