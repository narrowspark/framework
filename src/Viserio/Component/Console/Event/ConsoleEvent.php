<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
     *
     * @return null|\Symfony\Component\Console\Command\Command
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
