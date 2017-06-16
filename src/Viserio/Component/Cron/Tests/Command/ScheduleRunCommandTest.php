<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Cron\Command\ScheduleRunCommand;
use Viserio\Component\Cron\Schedule;

class ScheduleRunCommandTest extends MockeryTestCase
{
    public function testCommand()
    {
        $_SERVER['test'] = false;

        $schedule = new Schedule(__DIR__);
        $schedule->call(function () {
            $_SERVER['test'] = true;
        });

        $container = new ArrayContainer([
            Schedule::class => $schedule,
            'options'       => [
                'viserio' => [
                    'cron' => [
                        'env'         => 'test',
                        'maintenance' => false,
                    ],
                ],
            ],
        ]);

        $command = new ScheduleRunCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("Running scheduled command: Closure\n", $output);
        self::assertTrue($_SERVER['test']);

        unset($_SERVER['test']);
    }

    public function testCommandWithNoJobs()
    {
        $schedule = new Schedule(__DIR__);

        $container = new ArrayContainer([
            Schedule::class => $schedule,
            'options'       => [
                'viserio' => [
                    'cron' => [
                        'env'         => 'test',
                        'maintenance' => false,
                    ],
                ],
            ],
        ]);

        $command = new ScheduleRunCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("No scheduled commands are ready to run.\n", $output);
    }

    public function testCommandWithFalseFilter()
    {
        $schedule = new Schedule(__DIR__);
        $schedule->call(function () {
            return 'foo';
        })->when(function () {
            return false;
        });

        $container = new ArrayContainer([
            Schedule::class => $schedule,
            'options'       => [
                'viserio' => [
                    'cron' => [
                        'env'         => 'test',
                        'maintenance' => false,
                    ],
                ],
            ],
        ]);

        $command = new ScheduleRunCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals("No scheduled commands are ready to run.\n", $output);
    }
}
