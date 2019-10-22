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
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
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
     *
     * @return \Symfony\Component\Console\Output\OutputInterface
     */
    public function getSymfonyConsoleOutput(): OutputInterface
    {
        return $this->output;
    }
}
