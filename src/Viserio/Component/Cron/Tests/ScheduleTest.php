<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessUtils;
use Viserio\Component\Cron\Cron;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Cron\Tests\Fixture\ConsoleCerebroCommandFixture;
use Viserio\Component\Cron\Tests\Fixture\DummyClassFixture;

class ScheduleTest extends MockeryTestCase
{
    /**
     * Mocked CacheItemPoolInterface.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    public function setUp()
    {
        parent::setUp();

        $cache = $this->mock(CacheItemPoolInterface::class);

        $this->cache = $cache;
    }

    public function testExecCreatesNewCommand()
    {
        $schedule = new Schedule($this->cache, __DIR__);
        $schedule->exec('path/to/command');
        $schedule->exec('path/to/command -f --foo="bar"');
        $schedule->exec('path/to/command', ['-f']);
        $schedule->exec('path/to/command', ['--foo' => 'bar']);
        $schedule->exec('path/to/command', ['-f', '--foo' => 'bar']);
        $schedule->exec('path/to/command', ['--title' => 'A "real" test']);
        $schedule->exec('path/to/command', [['one', 'two']]);
        $schedule->exec('path/to/command', ['-1 minute']);

        $cronJobs = $schedule->getCronJobs();

        $escape     = '\\' === DIRECTORY_SEPARATOR ? '"' : '\'';
        $escapeReal = '\\' === DIRECTORY_SEPARATOR ? '\\"' : '"';

        self::assertEquals('path/to/command', $cronJobs[0]->getCommand());
        self::assertEquals('path/to/command -f --foo="bar"', $cronJobs[1]->getCommand());
        self::assertEquals('path/to/command -f', $cronJobs[2]->getCommand());
        self::assertEquals("path/to/command --foo={$escape}bar{$escape}", $cronJobs[3]->getCommand());
        self::assertEquals("path/to/command {$escape}-1 minute{$escape}", $cronJobs[7]->getCommand());
        self::assertEquals("path/to/command -f --foo={$escape}bar{$escape}", $cronJobs[4]->getCommand());
        self::assertEquals("path/to/command {$escape}one{$escape} {$escape}two{$escape}", $cronJobs[6]->getCommand());
        self::assertEquals("path/to/command --title={$escape}A {$escapeReal}real{$escapeReal} test{$escape}", $cronJobs[5]->getCommand());
    }

    public function testCommandCreatesNewCerebroCommand()
    {
        $schedule = new Schedule($this->cache, __DIR__, 'cerebro');

        $schedule->command('clear:view');
        $schedule->command('clear:view --tries=3');
        $schedule->command('clear:view', ['--tries' => 3]);

        $cronJobs = $schedule->getCronJobs();

        $escape = '\\' === DIRECTORY_SEPARATOR ? '"' : '\'';
        $binary = $escape . PHP_BINARY . $escape;

        if (getenv('TRAVIS')) {
            self::assertEquals($binary . ' \'cerebro\' clear:view', $cronJobs[0]->getCommand());
            self::assertEquals($binary . ' \'cerebro\' clear:view --tries=3', $cronJobs[1]->getCommand());
            self::assertEquals($binary . ' \'cerebro\' clear:view --tries=3', $cronJobs[2]->getCommand());
        } else {
            self::assertEquals($binary . ' "cerebro" clear:view', $cronJobs[0]->getCommand());
            self::assertEquals($binary . ' "cerebro" clear:view --tries=3', $cronJobs[1]->getCommand());
            self::assertEquals($binary . ' "cerebro" clear:view --tries=3', $cronJobs[2]->getCommand());
        }
    }

    public function testCreateNewCerebroCommandUsingCommandClass()
    {
        $schedule  = new Schedule($this->cache, __DIR__, 'cerebro');
        $container = new ArrayContainer([
            ConsoleCerebroCommandFixture::class => new ConsoleCerebroCommandFixture(
                new DummyClassFixture($schedule)
            ),
        ]);

        $binary = ProcessUtils::escapeArgument((new PhpExecutableFinder())->find(false));

        if (getenv('TRAVIS')) {
            $cron = new Cron($this->cache, $binary . ' \'cerebro\' foo:bar --force');
        } else {
            $cron = new Cron($this->cache, $binary . ' "cerebro" foo:bar --force');
        }

        $cron->setContainer($container)->setPath(__DIR__);

        $schedule->setContainer($container, 'cerebro');

        $schedule->command(ConsoleCerebroCommandFixture::class, ['--force']);

        $cronJobs = $schedule->getCronJobs();

        $escape = '\\' === DIRECTORY_SEPARATOR ? '"' : '\'';
        $binary = $escape . PHP_BINARY . $escape;

        if (getenv('TRAVIS')) {
            self::assertEquals($binary . ' \'cerebro\' foo:bar --force', $cronJobs[0]->getCommand());
        } else {
            self::assertEquals($binary . ' "cerebro" foo:bar --force', $cronJobs[0]->getCommand());
        }

        self::assertEquals([$cron], $schedule->dueCronJobs('test'));
    }

    public function testCreateNewCerebroCommandUsingCallBack()
    {
        $schedule = new Schedule($this->cache, __DIR__, 'cerebro');
        $schedule->setContainer(new ArrayContainer([]));
        $schedule->call(function () {
            return 'foo';
        });

        $cronJobs = $schedule->getCronJobs();

        self::assertSame('Closure', $cronJobs[0]->getSummaryForDisplay());
    }
}
