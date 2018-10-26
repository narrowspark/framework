<?php
declare(strict_types=1);
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
 */
final class ScheduleRunCommandTest extends MockeryTestCase
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

        $command = new ScheduleRunCommand();

        $this->invoker = new Invoker();
        $this->command = $command;
    }

    public function testCommand(): void
    {
        $_SERVER['test'] = false;

        $schedule = new Schedule(__DIR__);
        $schedule->call(function (): void {
            $_SERVER['test'] = true;
        });

        $container = new ArrayContainer([
            Schedule::class => $schedule,
            'config'        => [
                'viserio' => [
                    'cron' => [
                        'env'         => 'test',
                        'maintenance' => false,
                    ],
                ],
            ],
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        $this->assertEquals("Running scheduled command: Closure\n", $output);
        $this->assertTrue($_SERVER['test']);

        unset($_SERVER['test']);
    }

    public function testCommandWithNoJobs(): void
    {
        $schedule = new Schedule(__DIR__);

        $container = new ArrayContainer([
            Schedule::class => $schedule,
            'config'        => [
                'viserio' => [
                    'cron' => [
                        'env'         => 'test',
                        'maintenance' => false,
                    ],
                ],
            ],
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        $this->assertEquals("No scheduled commands are ready to run.\n", $output);
    }

    public function testCommandWithFalseFilter(): void
    {
        $schedule = new Schedule(__DIR__);
        $schedule->call(function () {
            return 'foo';
        })->when(function () {
            return false;
        });

        $container = new ArrayContainer([
            Schedule::class => $schedule,
            'config'        => [
                'viserio' => [
                    'cron' => [
                        'env'         => 'test',
                        'maintenance' => false,
                    ],
                ],
            ],
        ]);

        $this->arrangeInvoker($container);

        $tester = new CommandTester($this->command);
        $tester->execute([]);

        $output = $tester->getDisplay(true);

        $this->assertEquals("No scheduled commands are ready to run.\n", $output);
    }

    /**
     * @param \Psr\Container\ContainerInterface $container
     */
    private function arrangeInvoker(ContainerInterface $container): void
    {
        $this->command->setContainer($container);
        $this->invoker->setContainer($container)
            ->injectByTypeHint(true)
            ->injectByParameterName(true);
        $this->command->setInvoker($this->invoker);
    }
}
