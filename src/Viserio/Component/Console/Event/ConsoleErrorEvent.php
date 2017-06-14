<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Viserio\Component\Console\ConsoleEvents;

class ConsoleErrorEvent extends ConsoleEvent
{
    /**
     * A exception instance.
     *
     * @var \Throwable|null
     */
    private $error;

    /**
     * Is error handled.
     *
     * @var bool
     */
    private $handled = false;

    /**
     * Create a new console error event.
     *
     * @param \Symfony\Component\Console\Command\Command|null   $command
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable                                        $error
     * @param int                                               $exitCode
     */
    public function __construct(
        ?Command $command,
        InputInterface $input,
        OutputInterface $output,
        Throwable $error,
        int $exitCode
    ) {
        $this->name       = ConsoleEvents::ERROR;
        $this->target     = $command;
        $this->parameters = [
            'input'     => $input,
            'output'    => $output,
            'error'     => $error,
            'exit_code' => $exitCode,
        ];
    }

    /**
     * Returns the thrown exception.
     *
     * @return \Throwable The thrown exception
     */
    public function getError(): Throwable
    {
        return $this->error ?? $this->parameters['error'];
    }

    /**
     * Replaces the thrown exception.
     *
     * This exception will be thrown if no response is set in the event.
     *
     * @param \Throwable $exception The thrown exception
     * @param Throwable  $error
     *
     * @return void
     */
    public function setError(Throwable $error): void
    {
        $this->error = $error;
    }

    /**
     * Marks the error/exception as handled.
     *
     * If it is not marked as handled, the error/exception will be displayed in
     * the command output.
     *
     * @return void
     */
    public function markErrorAsHandled(): void
    {
        $this->handled = true;
    }

    /**
     * Whether the error/exception is handled by a listener or not.
     *
     * If it is not yet handled, the error/exception will be displayed in the
     * command output.
     *
     * @return bool
     */
    public function isErrorHandled(): bool
    {
        return $this->handled;
    }

    /**
     * Gets the exit code.
     *
     * @return int The command exit code
     */
    public function getExitCode(): int
    {
        return $this->parameters['exit_code'];
    }
}
