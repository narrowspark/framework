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

namespace Viserio\Component\Cron\Tests;

use Mockery;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Viserio\Component\Cron\Cron;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\Cron\Tests\Fixture\ConsoleCerebroCommandFixture;
use Viserio\Component\Cron\Tests\Fixture\DummyClassFixture;
use Viserio\Contract\Cron\Exception\LogicException;

/**
 * @internal
 *
 * @covers \Viserio\Component\Cron\Schedule
 *
 * @small
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

        $cache = Mockery::mock(CacheItemPoolInterface::class);

        $this->cache = $cache;
    }

    public function testExecCreatesNewCommand(): void
    {
        $schedule = new Schedule(__DIR__);
        $schedule->setCacheItemPool(Mockery::mock(CacheItemPoolInterface::class));

        $schedule->exec('path/to/command');
        $schedule->exec('path/to/command -f --foo="bar"');
        $schedule->exec('path/to/command', ['-f']);
        $schedule->exec('path/to/command', ['--foo' => 'bar']);
        $schedule->exec('path/to/command', ['-f', '--foo' => 'bar']);
        $schedule->exec('path/to/command', ['--title' => 'A "real" test']);
        $schedule->exec('path/to/command', [['one', 'two']]);
        $schedule->exec('path/to/command', ['-1 minute']);

        $cronJobs = $schedule->getCronJobs();

        $escape = \PHP_OS_FAMILY === 'Windows' ? '"' : '\'';
        $escapeReal = \PHP_OS_FAMILY === 'Windows' ? ' ' : '"';

        self::assertEquals('path/to/command', $cronJobs[0]->getCommand());
        self::assertEquals('path/to/command -f --foo="bar"', $cronJobs[1]->getCommand());
        self::assertEquals('path/to/command -f', $cronJobs[2]->getCommand());
        self::assertEquals("path/to/command --foo={$escape}bar{$escape}", $cronJobs[3]->getCommand());
        self::assertEquals("path/to/command {$escape}-1 minute{$escape}", $cronJobs[7]->getCommand());
        self::assertEquals("path/to/command -f --foo={$escape}bar{$escape}", $cronJobs[4]->getCommand());
        self::assertEquals("path/to/command {$escape}one{$escape} {$escape}two{$escape}", $cronJobs[6]->getCommand());
        self::assertEquals("path/to/command --title={$escape}A {$escapeReal}real{$escapeReal} test{$escape}", $cronJobs[5]->getCommand());
    }

    public function testCommandCreatesNewCerebroCommand(): void
    {
        $schedule = new Schedule(__DIR__, 'cerebro');
        $schedule->setCacheItemPool(Mockery::mock(CacheItemPoolInterface::class));

        $this->arrangeScheduleClearViewCommand($schedule);

        $cronJobs = $schedule->getCronJobs();

        $escape = \PHP_OS_FAMILY === 'Windows' ? '"' : '\'';
        $binary = $escape . \PHP_BINARY . $escape;

        self::assertEquals($binary . " {$escape}cerebro{$escape} clear:view", $cronJobs[0]->getCommand());
        self::assertEquals($binary . " {$escape}cerebro{$escape} clear:view --tries=3", $cronJobs[1]->getCommand());
        self::assertEquals($binary . " {$escape}cerebro{$escape} clear:view --tries=3", $cronJobs[2]->getCommand());
    }

    public function testCommandThrowException(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('You need to set a console name or a path to a console, before you call command.');

        $schedule = new Schedule(__DIR__);

        $schedule->command('clear:view');
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testCommandCreatesNewCerebroBinaryCommand(): void
    {
        \define('CEREBRO_BINARY', 'cerebro');

        $schedule = new Schedule(__DIR__);

        $this->arrangeScheduleClearViewCommand($schedule);

        $cronJobs = $schedule->getCronJobs();

        $escape = \PHP_OS_FAMILY === 'Windows' ? '"' : '\'';
        $binary = $escape . \PHP_BINARY . $escape;

        self::assertEquals($binary . " {$escape}cerebro{$escape} clear:view", $cronJobs[0]->getCommand());
        self::assertEquals($binary . " {$escape}cerebro{$escape} clear:view --tries=3", $cronJobs[1]->getCommand());
        self::assertEquals($binary . " {$escape}cerebro{$escape} clear:view --tries=3", $cronJobs[2]->getCommand());
    }

    public function testCreateNewCerebroCommandUsingCommandClass(): void
    {
        $schedule = new Schedule(__DIR__, 'cerebro');
        $container = new ArrayContainer([
            ConsoleCerebroCommandFixture::class => new ConsoleCerebroCommandFixture(
                new DummyClassFixture($schedule)
            ),
        ]);
        $finder = (new PhpExecutableFinder())->find(false);

        $binary = \escapeshellarg($finder === false ? '' : $finder);
        $escape = \PHP_OS_FAMILY === 'Windows' ? '"' : '\'';
        $cron = new Cron($binary . " {$escape}cerebro{$escape} foo:bar --force");

        $cron->setContainer($container)->setPath(__DIR__);

        $schedule->setContainer($container);

        $schedule->command(ConsoleCerebroCommandFixture::class, ['--force']);

        $cronJobs = $schedule->getCronJobs();

        $escape = \PHP_OS_FAMILY === 'Windows' ? '"' : '\'';
        $binary = $escape . \PHP_BINARY . $escape;

        self::assertEquals($binary . " {$escape}cerebro{$escape} foo:bar --force", $cronJobs[0]->getCommand());
        self::assertEquals([$cron], $schedule->dueCronJobs('test'));
    }

    public function testCreateNewCerebroCommandUsingCallBack(): void
    {
        $schedule = new Schedule(__DIR__, 'cerebro');
        $schedule->setCacheItemPool(Mockery::mock(CacheItemPoolInterface::class));
        $schedule->setContainer(new ArrayContainer([]));

        $schedule->call(static function () {
            return 'foo';
        });

        $cronJobs = $schedule->getCronJobs();

        self::assertSame('Closure', $cronJobs[0]->getSummaryForDisplay());
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
