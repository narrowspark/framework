<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Command;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\Cron\Command\CronListCommand;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Support\Invoker;

/**
 * @internal
 */
final class CronListCommandTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Console\Command\AbstractCommand
     */
    private $command;

    /**
     * @var \Viserio\Component\Support\Invoker
     */
    private $invoker;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $command = new CronListCommand();

        $this->invoker = new Invoker();
        $this->command = $command;
    }

    public function testCommand(): void
    {
        $schedule = new Schedule(__DIR__);
        $schedule->call(function () {
            return 'foo';
        });

        $container = new ArrayContainer([
            Schedule::class => $schedule,
        ]);

        $this->command->setContainer($container);
        $this->invoker->setContainer($container)
            ->injectByTypeHint(true)
            ->injectByParameterName(true);
        $this->command->setInvoker($this->invoker);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        static::assertEquals(
            "+---------+------------+---------+\n| Jobname | Expression | Summary |\n+---------+------------+---------+\n|         | * * * * *  | Closure |\n+---------+------------+---------+\n",
            $output
        );
    }
}
