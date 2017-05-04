<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Cron\Commands\CronListCommand;
use Viserio\Component\Cron\Schedule;

class CronListCommandTest extends MockeryTestCase
{
    public function testCommand()
    {
        $schedule = new Schedule(__DIR__);
        $schedule->call(function () {
            return 'foo';
        });

        $container = new ArrayContainer([
            Schedule::class => $schedule,
        ]);

        $command = new CronListCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        self::assertEquals(
            "+---------+-------------+---------+\n| Jobname | Expression  | Summary |\n+---------+-------------+---------+\n|         | * * * * * * | Closure |\n+---------+-------------+---------+\n",
            $output
        );
    }
}
