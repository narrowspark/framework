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

namespace Viserio\Component\Cron\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Cron\Command\ScheduleRunCommand;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 *
 * @covers \Viserio\Component\Cron\Command\ScheduleRunCommand
 *
 * @small
 */
final class ScheduleRunCommandTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Console\Command\AbstractCommand */
    private $command;

    /** @var \Viserio\Component\Support\Invoker */
    private $invoker;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $command = new ScheduleRunCommand('test', false);

        $this->invoker = new Invoker();
        $this->command = $command;
    }

    public function testCommand(): void
    {
        $_SERVER['test'] = false;

        $schedule = new Schedule(__DIR__);
        $schedule->call(static function (): void {
            $_SERVER['test'] = true;
        });

        $container = new ArrayContainer([
            Schedule::class => $schedule,
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Running scheduled command: Closure\n", $output);
        self::assertTrue($_SERVER['test']);

        unset($_SERVER['test']);
    }

    public function testCommandWithNoJobs(): void
    {
        $schedule = new Schedule(__DIR__);

        $container = new ArrayContainer([
            Schedule::class => $schedule,
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("No scheduled commands are ready to run.\n", $output);
    }

    public function testCommandWithFalseFilter(): void
    {
        $schedule = new Schedule(__DIR__);
        $schedule->call(static function () {
            return 'foo';
        })->when(static function () {
            return false;
        });

        $container = new ArrayContainer([
            Schedule::class => $schedule,
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("No scheduled commands are ready to run.\n", $output);
    }

    /**
     * @param \Psr\Container\ContainerInterface $container
     */
    private function arrangeInvoker(ContainerInterface $container): void
    {
        $this->invoker->setContainer($container)
            ->injectByTypeHint(true)
            ->injectByParameterName(true);
        $this->command->setInvoker($this->invoker);
    }
}
