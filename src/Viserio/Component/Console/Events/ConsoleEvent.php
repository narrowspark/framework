<?php
declare(strict_types=1);
namespace Viserio\Component\Console\Events;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Component\Contracts\Events\Event as EventContract;
use Viserio\Component\Events\Traits\EventTrait;

abstract class ConsoleEvent implements EventContract
{
    use EventTrait;

    /**
     * Gets the command that is executed.
     *
     * @return \Symfony\Component\Console\Command\Command|null A Command instance
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
