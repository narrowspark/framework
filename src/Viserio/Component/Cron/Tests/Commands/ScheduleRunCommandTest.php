<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Cron\Commands\ScheduleRunCommand;
use Viserio\Component\Cron\Schedule;

class ScheduleRunCommandTest extends MockeryTestCase
{
    public function testCommandWith()
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
}
