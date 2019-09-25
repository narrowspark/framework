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

namespace Viserio\Component\Console\Tests\Fixture;

use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LazyWhiner
{
    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private static $output;

    /**
     * @param ContainerInterface $instantiator
     */
    public function __construct(ContainerInterface $instantiator)
    {
        $instantiatorName = \get_class($instantiator);

        self::$output->write("LazyWhiner says:\n{$instantiatorName} woke me up! :-(\n\n");
    }

    /**
     * @return string
     */
    public static function getOutput(): string
    {
        return self::$output->output;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public static function setOutput(OutputInterface $output): void
    {
        self::$output = $output;
    }

    /**
     * @param object $runner
     *
     * @return void
     */
    public function whine(object $runner): void
    {
        $runnerName = \get_class($runner);

        self::$output->write("LazyWhiner says:\n{$runnerName} made me do work! :-(\n\n");
    }
}
