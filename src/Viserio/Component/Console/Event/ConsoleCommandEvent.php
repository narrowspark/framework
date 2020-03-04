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

namespace Viserio\Component\Console\Event;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
     */
    public function __construct(Command $command, InputInterface $input, OutputInterface $output)
    {
        $this->name = ConsoleEvents::COMMAND;
        $this->target = $command;
        $this->parameters = ['input' => $input, 'output' => $output];
    }

    /**
     * Returns true if the command is runnable, false otherwise.
     */
    public function commandShouldRun(): bool
    {
        return $this->commandShouldRun;
    }

    /**
     * Disables the command, so it won't be run.
     */
    public function disableCommand(): bool
    {
        return $this->commandShouldRun = false;
    }

    /**
     * Enables the command.
     */
    public function enableCommand(): bool
    {
        return $this->commandShouldRun = true;
    }
}
