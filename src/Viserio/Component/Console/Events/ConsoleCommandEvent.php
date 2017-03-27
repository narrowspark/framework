<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Events;

use Symfony\Component\Console\Command\Command;
use Viserio\Component\Console\ConsoleEvents;

class ConsoleCommandEvent extends ConsoleEvent
{
    /**
     * The return code for skipped commands, this will also be passed into the terminate event.
     */
    public const RETURN_CODE_DISABLED = 113;

    /**
     * Indicates if the command should be run or skipped.
     *
     * @var bool
     */
    private $commandShouldRun = true;

    /**
     * Create a new command event.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     * @param array                                      $params
     *
     * @codeCoverageIgnore
     */
    public function __construct(Command $command, array $params)
    {
        $this->name       = ConsoleEvents::COMMAND;
        $this->target     = $command;
        $this->parameters = $params;
    }

    /**
     * Disables the command, so it won't be run.
     *
     * @return bool
     */
    public function disableCommand()
    {
        return $this->commandShouldRun = false;
    }

    /**
     * Enables the command.
     *
     * @return bool
     */
    public function enableCommand()
    {
        return $this->commandShouldRun = true;
    }

    /**
     * Returns true if the command is runnable, false otherwise.
     *
     * @return bool
     */
    public function commandShouldRun()
    {
        return $this->commandShouldRun;
    }
}
