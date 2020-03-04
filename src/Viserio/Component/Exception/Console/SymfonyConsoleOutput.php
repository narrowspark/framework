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

namespace Viserio\Component\Exception\Console;

use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Contract\Exception\ConsoleOutput as ConsoleOutputContract;

final class SymfonyConsoleOutput implements ConsoleOutputContract
{
    /**
     * A symfony console output instance.
     *
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * Create a new symfony console output wrapper instance.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    public function writeln(string $message): void
    {
        $this->output->writeln($message);
    }

    /**
     * {@inheritdoc}
     */
    public function getVerbosity(): int
    {
        return $this->output->getVerbosity();
    }

    /**
     * Get the symfony output instance.
     */
    public function getSymfonyConsoleOutput(): OutputInterface
    {
        return $this->output;
    }
}
