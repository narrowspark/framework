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

namespace Viserio\Component\Console\Tests\Fixture;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LazyWhiner
{
    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private static $output;

    public function __construct(ContainerInterface $instantiator)
    {
        $instantiatorName = \get_class($instantiator);

        self::$output->write("LazyWhiner says:\n{$instantiatorName} woke me up! :-(\n\n");
    }

    public static function getOutput(): string
    {
        return self::$output->output;
    }

    public static function setOutput(OutputInterface $output): void
    {
        self::$output = $output;
    }

    public function whine(object $runner): void
    {
        $runnerName = \get_class($runner);

        self::$output->write("LazyWhiner says:\n{$runnerName} made me do work! :-(\n\n");
    }
}
