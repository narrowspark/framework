<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Viserio\Component\Cron\Cron;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Cron\Tests\Fixture\ConsoleCerebroCommandFixture;
use Viserio\Component\Cron\Tests\Fixture\DummyClassFixture;

/**
 * @internal
 */
final class ScheduleTest extends MockeryTestCase
{
    /**
     * Mocked CacheItemPoolInterface.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $cache = $this->mock(CacheItemPoolInterface::class);

        $this->cache = $cache;
    }

    public function testExecCreatesNewCommand(): void
    {
        $schedule = new Schedule(__DIR__);
        $schedule->setCacheItemPool($this->mock(CacheItemPoolInterface::class));

        $schedule->exec('path/to/command');
        $schedule->exec('path/to/command -f --foo="bar"');
        $schedule->exec('path/to/command', ['-f']);
        $schedule->exec('path/to/command', ['--foo' => 'bar']);
        $schedule->exec('path/to/command', ['-f', '--foo' => 'bar']);
        $schedule->exec('path/to/command', ['--title' => 'A "real" test']);
        $schedule->exec('path/to/command', [['one', 'two']]);
        $schedule->exec('path/to/command', ['-1 minute']);

        $cronJobs = $schedule->getCronJobs();

        $escape     = '\\' === \DIRECTORY_SEPARATOR ? '"' : '\'';
        $escapeReal = '\\' === \DIRECTORY_SEPARATOR ? ' ' : '"';

        static::assertEquals('path/to/command', $cronJobs[0]->getCommand());
        static::assertEquals('path/to/command -f --foo="bar"', $cronJobs[1]->getCommand());
        static::assertEquals('path/to/command -f', $cronJobs[2]->getCommand());
        static::assertEquals("path/to/command --foo={$escape}bar{$escape}", $cronJobs[3]->getCommand());
        static::assertEquals("path/to/command {$escape}-1 minute{$escape}", $cronJobs[7]->getCommand());
        static::assertEquals("path/to/command -f --foo={$escape}bar{$escape}", $cronJobs[4]->getCommand());
        static::assertEquals("path/to/command {$escape}one{$escape} {$escape}two{$escape}", $cronJobs[6]->getCommand());
        static::assertEquals("path/to/command --title={$escape}A {$escapeReal}real{$escapeReal} test{$escape}", $cronJobs[5]->getCommand());
    }

    public function testCommandCreatesNewCerebroCommand(): void
    {
        $schedule = new Schedule(__DIR__, 'cerebro');
        $schedule->setCacheItemPool($this->mock(CacheItemPoolInterface::class));

        $this->arrangeScheduleClearViewCommand($schedule);

        $cronJobs = $schedule->getCronJobs();

        $escape = '\\' === \DIRECTORY_SEPARATOR ? '"' : '\'';
        $binary = $escape . \PHP_BINARY . $escape;

        static::assertEquals($binary . " {$escape}cerebro{$escape} clear:view", $cronJobs[0]->getCommand());
        static::assertEquals($binary . " {$escape}cerebro{$escape} clear:view --tries=3", $cronJobs[1]->getCommand());
        static::assertEquals($binary . " {$escape}cerebro{$escape} clear:view --tries=3", $cronJobs[2]->getCommand());
    }

    public function testCommandThrowException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You need to set a console name or a path to a console, before you call command.');

        $schedule = new Schedule(__DIR__);

        $schedule->command('clear:view');
    }

    public function testCommandCreatesNewCerebroBinaryCommand(): void
    {
        \define('CEREBRO_BINARY', 'cerebro');

        $schedule = new Schedule(__DIR__);

        $this->arrangeScheduleClearViewCommand($schedule);

        $cronJobs = $schedule->getCronJobs();

        $escape = '\\' === \DIRECTORY_SEPARATOR ? '"' : '\'';
        $binary = $escape . \PHP_BINARY . $escape;

        static::assertEquals($binary . " {$escape}cerebro{$escape} clear:view", $cronJobs[0]->getCommand());
        static::assertEquals($binary . " {$escape}cerebro{$escape} clear:view --tries=3", $cronJobs[1]->getCommand());
        static::assertEquals($binary . " {$escape}cerebro{$escape} clear:view --tries=3", $cronJobs[2]->getCommand());
    }

    public function testCreateNewCerebroCommandUsingCommandClass(): void
    {
        $schedule  = new Schedule(__DIR__, 'cerebro');
        $container = new ArrayContainer([
            ConsoleCerebroCommandFixture::class => new ConsoleCerebroCommandFixture(
                new DummyClassFixture($schedule)
            ),
        ]);
        $finder = (new PhpExecutableFinder())->find(false);

        $binary = \escapeshellarg($finder === false ? '' : $finder);
        $escape = '\\' === \DIRECTORY_SEPARATOR ? '"' : '\'';
        $cron   = new Cron($binary . " {$escape}cerebro{$escape} foo:bar --force");

        $cron->setContainer($container)->setPath(__DIR__);

        $schedule->setContainer($container);

        $schedule->command(ConsoleCerebroCommandFixture::class, ['--force']);

        $cronJobs = $schedule->getCronJobs();

        $escape = '\\' === \DIRECTORY_SEPARATOR ? '"' : '\'';
        $binary = $escape . \PHP_BINARY . $escape;

        static::assertEquals($binary . " {$escape}cerebro{$escape} foo:bar --force", $cronJobs[0]->getCommand());
        static::assertEquals([$cron], $schedule->dueCronJobs('test'));
    }

    public function testCreateNewCerebroCommandUsingCallBack(): void
    {
        $schedule = new Schedule(__DIR__, 'cerebro');
        $schedule->setCacheItemPool($this->mock(CacheItemPoolInterface::class));
        $schedule->setContainer(new ArrayContainer([]));

        $schedule->call(function () {
            return 'foo';
        });

        $cronJobs = $schedule->getCronJobs();

        static::assertSame('Closure', $cronJobs[0]->getSummaryForDisplay());
    }

    /**
     * @param \Viserio\Component\Cron\Schedule $schedule
     */
    private function arrangeScheduleClearViewCommand(Schedule $schedule): void
    {
        $schedule->command('clear:view');
        $schedule->command('clear:view --tries=3');
        $schedule->command('clear:view', ['--tries' => 3]);
    }
}
