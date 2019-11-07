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

use Cake\Chronos\Chronos;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cron\Cron;

/**
 * @coversDefaultClass \Viserio\Component\Cron\Cron
 *
 * @internal
 *
 * @small
 */
final class CronTest extends MockeryTestCase
{
    /**
     * The default configuration timezone.
     *
     * @var string
     */
    protected $defaultTimezone;

    /**
     * Mocked CacheItemPoolInterface.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->defaultTimezone = \date_default_timezone_get();

        \date_default_timezone_set('UTC');

        $cache = Mockery::mock(CacheItemPoolInterface::class);

        $this->cache = $cache;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \date_default_timezone_set($this->defaultTimezone);

        Chronos::setTestNow(null);
    }

    public function testBasicCronCompilation(): void
    {
        $cron = new Cron('php foo');

        self::assertEquals('* * * * *', $cron->getExpression());
        self::assertTrue($cron->isDue('test'));
        self::assertTrue($cron->skip(static function () {
            return true;
        })->isDue('test'));
        self::assertFalse($cron->skip(static function () {
            return true;
        })->filtersPass());

        $cron = new Cron('php foo');

        self::assertEquals('* * * * *', $cron->getExpression());
        self::assertFalse($cron->setEnvironments('local')->isDue('test'));

        $cron = new Cron('php foo');

        self::assertEquals('* * * * *', $cron->getExpression());
        self::assertFalse($cron->when(static function () {
            return false;
        })->filtersPass());
    }

    public function testCronChainedRulesShouldBeCommutative(): void
    {
        $cronA = new Cron('php foo');
        $cronB = new Cron('php foo');

        self::assertEquals(
            $cronA->daily()->hourly()->getExpression(),
            $cronB->hourly()->daily()->getExpression()
        );

        $cronA = new Cron('php foo');
        $cronB = new Cron('php foo');

        self::assertEquals(
            $cronA->weekdays()->hourly()->getExpression(),
            $cronB->hourly()->weekdays()->getExpression()
        );
    }

    public function testGetExpression(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * *', $cron->getExpression());
    }

    public function testCron(): void
    {
        $cron = new Cron('');
        $cron->cron('*');

        self::assertSame('*', $cron->getExpression());
    }

    public function testHourly(): void
    {
        $cron = new Cron('');

        self::assertSame('0 * * * *', $cron->hourly()->getExpression());
    }

    public function testDaily(): void
    {
        $cron = new Cron('');

        self::assertSame('0 0 * * *', $cron->daily()->getExpression());
    }

    public function testMonthly(): void
    {
        $cron = new Cron('');

        self::assertSame('0 0 1 * *', $cron->monthly()->getExpression());
    }

    public function testYearly(): void
    {
        $cron = new Cron('');

        self::assertSame('0 0 1 1 *', $cron->yearly()->getExpression());
    }

    public function testQuarterly(): void
    {
        $cron = new Cron('');

        self::assertSame('0 0 1 */3 *', $cron->quarterly()->getExpression());
    }

    public function testEveryMinute(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * *', $cron->everyMinute()->getExpression());
    }

    public function testEveryFiveMinutes(): void
    {
        $cron = new Cron('');

        self::assertSame('*/5 * * * *', $cron->everyFiveMinutes()->getExpression());
    }

    public function testEveryTenMinutes(): void
    {
        $cron = new Cron('');

        self::assertSame('*/10 * * * *', $cron->everyTenMinutes()->getExpression());
    }

    public function testEveryThirtyMinutes(): void
    {
        $cron = new Cron('');

        self::assertSame('0,30 * * * *', $cron->everyThirtyMinutes()->getExpression());
    }

    public function testDays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 1', $cron->days(1)->getExpression());
    }

    public function testMonthlyOn(): void
    {
        $cron = new Cron('');

        self::assertSame('0 15 4 * *', $cron->monthlyOn(4, '15:00')->getExpression());
    }

    public function testDailyAt(): void
    {
        $cron = new Cron('');

        self::assertSame('30 10 * * *', $cron->dailyAt('10:30')->getExpression());
    }

    public function testTwiceDaily(): void
    {
        $cron = new Cron('');

        self::assertSame('0 1,13 * * *', $cron->twiceDaily()->getExpression());
    }

    public function testTwiceMonthly(): void
    {
        $cron = new Cron('');

        self::assertEquals('0 0 1,16 * *', $cron->twiceMonthly(1, 16)->getExpression());
    }

    public function testWeekdays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 1-5', $cron->weekdays()->getExpression());
    }

    public function testMondays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 1', $cron->mondays()->getExpression());
    }

    public function testTuesdays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 2', $cron->tuesdays()->getExpression());
    }

    public function testWednesdays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 3', $cron->wednesdays()->getExpression());
    }

    public function testThursdays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 4', $cron->thursdays()->getExpression());
    }

    public function testFridays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 5', $cron->fridays()->getExpression());
    }

    public function testSaturdays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 6', $cron->saturdays()->getExpression());
    }

    public function testSundays(): void
    {
        $cron = new Cron('');

        self::assertSame('* * * * 0', $cron->sundays()->getExpression());
    }

    public function testWeekly(): void
    {
        $cron = new Cron('');

        self::assertSame('0 0 * * 0', $cron->weekly()->getExpression());
    }

    public function testWeeklyOn(): void
    {
        $cron = new Cron('');

        self::assertSame('0 0 * * 1', $cron->weeklyOn(1)->getExpression());
    }

    /**
     * @covers ::ensureCorrectUser
     */
    public function testBuildCommand(): void
    {
        $quote = (\DIRECTORY_SEPARATOR === '\\') ? '"' : "'";

        $cron = new Cron('php -i');
        $isWindows = \stripos(\PHP_OS, 'win') === 0;
        $defaultOutput = $isWindows ? 'NUL' : '/dev/null';
        $windows = $isWindows ? 'start /B ' : '';
        $background = $isWindows ? '' : ' &';

        self::assertSame("{$windows}php -i > {$quote}{$defaultOutput}{$quote} 2>&1{$background}", $cron->buildCommand());
    }

    public function testGetAndSetUser(): void
    {
        $cron = new Cron('');

        self::assertSame('root', $cron->setUser('root')->getUser());
    }

    public function testGetAndSetPath(): void
    {
        $cron = new Cron('');

        self::assertSame(__DIR__, $cron->setPath(__DIR__)->getPath());
    }

    public function testEnvironments(): void
    {
        $cron = new Cron('');

        $cron->setEnvironments(['dev', 'prod']);

        self::assertTrue($cron->runsInEnvironment('dev'));
    }

    public function testBuildCommandSendOutputTo(): void
    {
        $quote = (\DIRECTORY_SEPARATOR === '\\') ? '"' : "'";
        $isWindows = \stripos(\PHP_OS, 'win') === 0;
        $windows = $isWindows ? 'start /B ' : '';
        $background = $isWindows ? '' : ' &';

        $cron = new Cron('php -i');
        $cron->sendOutputTo('/dev/null');

        self::assertSame("{$windows}php -i > {$quote}/dev/null{$quote} 2>&1{$background}", $cron->buildCommand());

        $cron = new Cron('php -i');
        $cron->sendOutputTo('/my folder/foo.log');

        self::assertSame("{$windows}php -i > {$quote}/my folder/foo.log{$quote} 2>&1{$background}", $cron->buildCommand());
    }

    public function testBuildCommandAppendOutput(): void
    {
        $quote = (\DIRECTORY_SEPARATOR === '\\') ? '"' : "'";
        $isWindows = \stripos(\PHP_OS, 'win') === 0;
        $windows = $isWindows ? 'start /B ' : '';
        $background = $isWindows ? '' : ' &';

        $cron = new Cron('php -i');
        $cron->appendOutputTo('/dev/null');

        self::assertSame("{$windows}php -i >> {$quote}/dev/null{$quote} 2>&1{$background}", $cron->buildCommand());
    }

    public function testGetSummaryForDisplay(): void
    {
        $cron = new Cron('php -i');

        self::assertSame($cron->buildCommand(), $cron->getSummaryForDisplay());

        $cron->setDescription('test');

        self::assertSame('test', $cron->getSummaryForDisplay());
    }

    public function testTimeBetweenChecks(): void
    {
        Chronos::setTestNow(Chronos::now()->startOfDay()->addHours(9));

        $cron = new Cron('php foo');
        $cron->setTimezone('UTC');

        self::assertTrue($cron->between('8:00', '10:00')->filtersPass());
        self::assertTrue($cron->between('9:00', '9:00')->filtersPass());
        self::assertFalse($cron->between('10:00', '11:00')->filtersPass());
        self::assertFalse($cron->unlessBetween('8:00', '10:00')->filtersPass());
        self::assertTrue($cron->unlessBetween('10:00', '11:00')->isDue('test'));
    }

    public function testCronJobIsDueCheck(): void
    {
        Chronos::setTestNow(Chronos::create(2015, 1, 1, 0, 0, 0));

        $cron = new Cron('php foo');
        $cron->setTimezone('Europe/Berlin');

        self::assertEquals('* * * * 4', $cron->thursdays()->getExpression());
        self::assertTrue($cron->isDue('test'));

        self::assertFalse($cron->isDue('test', true));

        $cron->evenInMaintenanceMode();

        self::assertTrue($cron->isDue('test', true));
    }

    public function testCronRun(): void
    {
        $_SERVER['test'] = false;

        $cron = new Cron('php -i');

        $cron->before(static function (): void {
            $_SERVER['test'] = 'before';
        });
        $cron->after(static function (): void {
            $_SERVER['test'] = $_SERVER['test'] . ' after';
        });

        // OK
        self::assertSame(0, $cron->run());

        self::assertSame('before after', $_SERVER['test']);

        unset($_SERVER['test']);
    }

    public function testCronRunInBackground(): void
    {
        $cron = new Cron('ls -lsa');
        $cron->runInBackground();

        // OK
        self::assertSame(0, $cron->run());
    }

    public function testCronRunWithoutOverlapping(): void
    {
        $name = 'schedule-' . \sha1('* * * * *ls -lsa');
        $item = Mockery::mock(CacheItemInterface::class);
        $item->shouldReceive('set')
            ->once()
            ->with($name);
        $item->shouldReceive('expiresAfter')
            ->once()
            ->with(1440);
        $cache = Mockery::mock(CacheItemPoolInterface::class);
        $cache->shouldReceive('getItem')
            ->once()
            ->andReturn($item);
        $cache->shouldReceive('save')
            ->once()
            ->with($item);
        $cache->shouldReceive('deleteItem')
            ->once()
            ->with($name);

        $cron = new Cron('ls -lsa');
        $cron->setCacheItemPool($cache)
            ->withoutOverlapping()
            ->runInBackground();

        // OK
        self::assertSame(0, $cron->run());
    }

    public function testTimeBetweenBeforeAndAfterMidnight(): void
    {
        Chronos::setTestNow(Chronos::now()->startOfDay()->addHours(22));

        $cron = new Cron('php foo');
        $cron->setTimezone('UTC');

        self::assertTrue($cron->between('21:00', '01:00')->filtersPass());
        self::assertFalse($cron->between('01:00', '21:00')->filtersPass());
        self::assertFalse($cron->between('23:00', '01:00')->filtersPass());

        self::assertFalse($cron->unlessBetween('21:00', '01:00')->filtersPass());
        self::assertTrue($cron->unlessBetween('23:00', '01:00')->isDue('prod'));
    }
}
