<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Events;

use Symfony\Component\Console\Command\Command;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Events\Traits\EventTrait;

class CommandTerminatingEvent implements EventContract
{
    use EventTrait;

    /**
     * The exit code of the command.
     *
     * @var int
     */
    private $exitCode;

    /**
     * Create a new command terminating event.
     *
     * @param \Symfony\Component\Console\Command\Command $command
     * @param array                                      $params
     *
     * @codeCoverageIgnore
     */
    public function __construct(Command $command, array $params)
    {
        $this->name       = 'command.terminating';
        $this->target     = $command;
        $this->parameters = $params;
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
        $this->exitCode = $exitCode;
    }

    /**
     * Gets the exit code.
     *
     * @return int The command exit code
     */
    public function getExitCode(): int
    {
        return $this->exitCode ?? $this->parameters['exit_code'];
    }
}
