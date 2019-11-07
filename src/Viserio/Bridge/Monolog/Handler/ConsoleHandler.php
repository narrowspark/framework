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

namespace Viserio\Bridge\Monolog\Handler;

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Bridge\Monolog\Formatter\ConsoleFormatter;
use Viserio\Component\Console\ConsoleEvents;
use Viserio\Component\Console\Event\ConsoleCommandEvent;
use Viserio\Contract\Events\EventManager as EventManagerContract;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;

/**
 * Writes logs to the console output depending on its verbosity setting.
 *
 * It is disabled by default and gets activated as soon as a command is executed.
 * Instead of listening to the console events, the output can also be set manually.
 *
 * The minimum logging level at which this handler will be triggered depends on the
 * verbosity setting of the console output. The default mapping is:
 * - OutputInterface::VERBOSITY_NORMAL will show all WARNING and higher logs
 * - OutputInterface::VERBOSITY_VERBOSE (-v) will show all NOTICE and higher logs
 * - OutputInterface::VERBOSITY_VERY_VERBOSE (-vv) will show all INFO and higher logs
 * - OutputInterface::VERBOSITY_DEBUG (-vvv) will show all DEBUG and higher logs, i.e. all logs
 *
 * This mapping can be customized with the $verbosityLevelMap constructor parameter.
 *
 * @author Tobias Schultze <http://tobion.de>
 */
class ConsoleHandler extends AbstractProcessingHandler
{
    use EventManagerAwareTrait;

    /**
     * A output instance.
     *
     * @var null|\Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * A verbosity level to monolog level mapper.
     *
     * @var array
     */
    private $verbosityLevelMap = [
        OutputInterface::VERBOSITY_QUIET => Logger::ERROR,
        OutputInterface::VERBOSITY_NORMAL => Logger::WARNING,
        OutputInterface::VERBOSITY_VERBOSE => Logger::NOTICE,
        OutputInterface::VERBOSITY_VERY_VERBOSE => Logger::INFO,
        OutputInterface::VERBOSITY_DEBUG => Logger::DEBUG,
    ];

    /**
     * Constructor.
     *
     * @param null|OutputInterface $output            The console output to use (the handler remains disabled when passing null
     *                                                until the output is set, e.g. by using console events)
     * @param bool                 $bubble            Whether the messages that are handled can bubble up the stack
     * @param array                $verbosityLevelMap Array that maps the OutputInterface verbosity to a minimum logging
     *                                                level (leave empty to use the default mapping)
     */
    public function __construct(?OutputInterface $output = null, $bubble = true, array $verbosityLevelMap = [])
    {
        parent::__construct(Logger::DEBUG, $bubble);

        $this->output = $output;

        if (\count($verbosityLevelMap) !== 0) {
            $this->verbosityLevelMap = $verbosityLevelMap;
        }
    }

    /**
     * Sets the console output to use for printing logs.
     *
     * @param OutputInterface $output The console output to use
     */
    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function isHandling(array $record): bool
    {
        return $this->updateLevel() && parent::isHandling($record);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(array $record): bool
    {
        // we have to update the logging level each time because the verbosity of the
        // console output might have changed in the meantime (it is not immutable)
        return $this->updateLevel() && parent::handle($record);
    }

    /**
     * Disables the output.
     */
    public function close(): void
    {
        $this->output = null;

        parent::close();
    }

    /**
     * Register needed events to event manager.
     *
     * @param \Viserio\Contract\Events\EventManager $eventManager
     *
     * @return void
     */
    public function registerEvents(EventManagerContract $eventManager): void
    {
        // Before a command is executed, the handler gets activated and the console output
        // is set in order to know where to write the logs.
        $eventManager->attach(
            ConsoleEvents::COMMAND,
            function (ConsoleCommandEvent $event): void {
                $output = $event->getOutput();

                if ($output instanceof ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();
                }

                $this->setOutput($output);
            },
            255
        );

        // After a command has been executed, it disables the output.
        $eventManager->attach(
            ConsoleEvents::TERMINATE,
            function (): void {
                $this->close();
            },
            -255
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record): void
    {
        if ($this->output === null) {
            return;
        }

        // at this point we've determined for sure that we want to output the record, so use the output's own verbosity
        $this->output->write((string) $record['formatted'], false, $this->output->getVerbosity());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultFormatter(): FormatterInterface
    {
        if ($this->output === null) {
            return new ConsoleFormatter();
        }

        return new ConsoleFormatter([
            'colors' => $this->output->isDecorated(),
            'multiline' => OutputInterface::VERBOSITY_DEBUG <= $this->output->getVerbosity(),
        ]);
    }

    /**
     * Updates the logging level based on the verbosity setting of the console output.
     *
     * @return bool Whether the handler is enabled and verbosity is not set to quiet
     */
    private function updateLevel(): bool
    {
        if ($this->output === null) {
            return false;
        }

        $verbosity = $this->output->getVerbosity();

        if (isset($this->verbosityLevelMap[$verbosity])) {
            $this->setLevel($this->verbosityLevelMap[$verbosity]);
        } else {
            $this->setLevel(Logger::DEBUG);
        }

        return true;
    }
}
