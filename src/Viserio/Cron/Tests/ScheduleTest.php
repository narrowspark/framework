<?php
declare(strict_types=1);
namespace Viserio\Cron\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessUtils;
use Viserio\Cron\Cron;
use Viserio\Cron\Schedule;
use Viserio\Cron\Tests\Fixture\ConsoleCerebroCommandFixture;
use Viserio\Cron\Tests\Fixture\DummyClassFixture;

class ScheduleTest extends \PHPUnit_Framework_TestCase
{
    public function testExecCreatesNewCommand()
    {
        $schedule = new Schedule();
        $schedule->exec('path/to/command');
        $schedule->exec('path/to/command -f --foo="bar"');
        $schedule->exec('path/to/command', ['-f']);
        $schedule->exec('path/to/command', ['--foo' => 'bar']);
        $schedule->exec('path/to/command', ['-f', '--foo' => 'bar']);
        $schedule->exec('path/to/command', ['--title' => 'A "real" test']);
        $schedule->exec('path/to/command', [['one', 'two']]);
        $schedule->exec('path/to/command', ['-1 minute']);

        $cronJobs = $schedule->getCronJobs();

        $escape = '\\' === DIRECTORY_SEPARATOR ? '"' : '\'';
        $escapeReal = '\\' === DIRECTORY_SEPARATOR ? '\\"' : '"';

        $this->assertEquals('path/to/command', $cronJobs[0]->getCommand());
        $this->assertEquals('path/to/command -f --foo="bar"', $cronJobs[1]->getCommand());
        $this->assertEquals('path/to/command -f', $cronJobs[2]->getCommand());
        $this->assertEquals("path/to/command --foo={$escape}bar{$escape}", $cronJobs[3]->getCommand());
        $this->assertEquals("path/to/command {$escape}-1 minute{$escape}", $cronJobs[7]->getCommand());
        $this->assertEquals("path/to/command -f --foo={$escape}bar{$escape}", $cronJobs[4]->getCommand());
        $this->assertEquals("path/to/command {$escape}one{$escape} {$escape}two{$escape}", $cronJobs[6]->getCommand());
        $this->assertEquals("path/to/command --title={$escape}A {$escapeReal}real{$escapeReal} test{$escape}", $cronJobs[5]->getCommand());
    }

    public function testCommandCreatesNewCerebroCommand()
    {
        $schedule = new Schedule();
        $schedule->setConsoleName('cerebro');

        $schedule->command('clear:view');
        $schedule->command('clear:view --tries=3');
        $schedule->command('clear:view', ['--tries' => 3]);

        $cronJobs = $schedule->getCronJobs();

        $escape = '\\' === DIRECTORY_SEPARATOR ? '"' : '\'';
        $binary = $escape . PHP_BINARY . $escape;

        if (getenv('TRAVIS')) {
            $this->assertEquals($binary . ' \'cerebro\' clear:view', $cronJobs[0]->getCommand());
            $this->assertEquals($binary . ' \'cerebro\' clear:view --tries=3', $cronJobs[1]->getCommand());
            $this->assertEquals($binary . ' \'cerebro\' clear:view --tries=3', $cronJobs[2]->getCommand());
        } else {
            $this->assertEquals($binary . ' "cerebro" clear:view', $cronJobs[0]->getCommand());
            $this->assertEquals($binary . ' "cerebro" clear:view --tries=3', $cronJobs[1]->getCommand());
            $this->assertEquals($binary . ' "cerebro" clear:view --tries=3', $cronJobs[2]->getCommand());
        }
    }

    public function testCreateNewCerebroCommandUsingCommandClass()
    {
        $schedule = new Schedule();
        $container = new ArrayContainer([
            ConsoleCerebroCommandFixture::class => new ConsoleCerebroCommandFixture(
                new DummyClassFixture($schedule)
            ),
        ]);

        $binary = ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));

        if (getenv('TRAVIS')) {
            $cron = new Cron($binary . ' \'cerebro\' foo:bar --force');
        } else {
            $cron = new Cron($binary . ' "cerebro" foo:bar --force');
        }

        $cron->setContainer($container);

        $schedule->setContainer($container);
        $schedule->setConsoleName('cerebro');

        $schedule->command(ConsoleCerebroCommandFixture::class, ['--force']);

        $cronJobs = $schedule->getCronJobs();

        $escape = '\\' === DIRECTORY_SEPARATOR ? '"' : '\'';
        $binary = $escape . PHP_BINARY . $escape;

        if (getenv('TRAVIS')) {
            $this->assertEquals($binary . ' \'cerebro\' foo:bar --force', $cronJobs[0]->getCommand());
        } else {
            $this->assertEquals($binary . ' "cerebro" foo:bar --force', $cronJobs[0]->getCommand());
        }

        $this->assertEquals([$cron], $schedule->dueCronJobs('test'));
    }

    public function testCreateNewCerebroCommandUsingCallBack()
    {
        $schedule = new Schedule();
        $schedule->setContainer(new ArrayContainer([]));
        $schedule->setConsoleName('cerebro');
        $schedule->call(function () {
            return 'foo';
        });

        $cronJobs = $schedule->getCronJobs();

        $this->assertSame('Closure', $cronJobs[0]->getSummaryForDisplay());
    }
}
