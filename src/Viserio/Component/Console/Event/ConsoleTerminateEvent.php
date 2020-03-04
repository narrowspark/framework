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

class ConsoleTerminateEvent extends ConsoleEvent
{
    /**
     * Create a new console terminate event.
     */
    public function __construct(?Command $command, InputInterface $input, OutputInterface $output, int $exitCode)
    {
        $this->name = ConsoleEvents::TERMINATE;
        $this->target = $command;
        $this->parameters = [
            'input' => $input,
            'output' => $output,
            'exit_code' => $exitCode,
        ];
    }

    /**
     * Sets the exit code.
     *
     * @param int $exitCode The command exit code
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
