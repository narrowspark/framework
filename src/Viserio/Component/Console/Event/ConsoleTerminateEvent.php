<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Component\Console\ConsoleEvents;

class ConsoleTerminateEvent extends ConsoleEvent
{
    /**
     * Create a new console terminate event.
     *
     * @param null|\Symfony\Component\Console\Command\Command   $command
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int                                               $exitCode
     */
    public function __construct(
        ?Command $command,
        InputInterface $input,
        OutputInterface $output,
        int $exitCode
    ) {
        $this->name       = ConsoleEvents::TERMINATE;
        $this->target     = $command;
        $this->parameters = [
            'input'     => $input,
            'output'    => $output,
            'exit_code' => $exitCode,
        ];
    }

    /**
     * Sets the exit code.
     *
     * @param int $exitCode The command exit code
     *
     * @return void
     */
    public function setExitCode(int $exitCode): void
    {
        $this->parameters['exit_code'] = $exitCode;
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
