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
use Viserio\Component\Events\Traits\EventTrait;
use Viserio\Contract\Events\Event as EventContract;

abstract class ConsoleEvent implements EventContract
{
    use EventTrait;

    /**
     * Gets the command that is executed.
     */
    public function getCommand(): ?Command
    {
        return $this->target;
    }

    /**
     * Gets the input instance.
     *
     * @return \Symfony\Component\Console\Input\InputInterface An InputInterface instance
     */
    public function getInput(): InputInterface
    {
        return $this->parameters['input'];
    }

    /**
     * Gets the output instance.
     *
     * @return \Symfony\Component\Console\Output\OutputInterface An OutputInterface instance
     */
    public function getOutput(): OutputInterface
    {
        return $this->parameters['output'];
    }
}
