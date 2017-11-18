<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Event;

use ReflectionProperty;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use Viserio\Component\Console\ConsoleEvents;

final class ConsoleErrorEvent extends ConsoleEvent
{
    /**
     * Create a new console error event.
     *
     * @param null|\Symfony\Component\Console\Command\Command   $command
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Throwable                                        $error
     */
    public function __construct(
        ?Command $command,
        InputInterface $input,
        OutputInterface $output,
        Throwable $error
    ) {
        $this->name       = ConsoleEvents::ERROR;
        $this->target     = $command;
        $this->parameters = [
            'input'     => $input,
            'output'    => $output,
            'error'     => $error,
            'exit_code' => $error->getCode() ?: 1,
        ];
    }

    /**
     * Returns the thrown exception.
     *
     * @return \Throwable The thrown exception
     */
    public function getError(): Throwable
    {
        return $this->parameters['error'];
    }

    /**
     * Replaces the thrown exception.
     *
     * This exception will be thrown if no response is set in the event.
     *
     * @param \Throwable $error
     *
     * @return void
     */
    public function setError(Throwable $error): void
    {
        $this->parameters['error']     = $error;
        $this->parameters['exit_code'] = $error->getCode() ?: 1;
    }

    /**
     * Sets the exit code.
     *
     * @param int $exitCode The command exit code
     *
     * @throws \ReflectionException
     *
     * @return void
     */
    public function setExitCode(int $exitCode): void
    {
        $this->parameters['exit_code'] = $exitCode;

        $r = new ReflectionProperty($this->parameters['error'], 'code');
        $r->setAccessible(true);
        $r->setValue($this->parameters['error'], $this->parameters['exit_code']);
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
