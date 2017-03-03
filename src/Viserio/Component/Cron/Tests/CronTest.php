<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests;

use Cake\Chronos\Chronos;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cron\Cron;

class CronTest extends MockeryTestCase
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

    public function setUp()
    {
        parent::setUp();

        $this->defaultTimezone = date_default_timezone_get();

        date_default_timezone_set('UTC');

        $cache = $this->mock(CacheItemPoolInterface::class);

        $this->cache = $cache;
    }

    public function tearDown()
    {
        date_default_timezone_set($this->defaultTimezone);

        Chronos::setTestNow(null);

        parent::tearDown();
    }

    public function testBasicCronCompilation()
    {
        $cron = new Cron($this->cache, 'php foo');

        self::assertEquals('* * * * * *', $cron->getExpression());
        self::assertTrue($cron->isDue('test'));
        self::assertTrue($cron->skip(function () {
            return true;
        })->isDue('test'));
        self::assertFalse($cron->skip(function () {
            return true;
        })->filtersPass());

        $cron = new Cron($this->cache, 'php foo');

        self::assertEquals('* * * * * *', $cron->getExpression());
        self::assertFalse($cron->setEnvironments('local')->isDue('test'));

        $cron = new Cron($this->cache, 'php foo');

        self::assertEquals('* * * * * *', $cron->getExpression());
        self::assertFalse($cron->when(function () {
            return false;
        })->filtersPass());
    }

    public function testCronChainedRulesShouldBeCommutative()
    {
        $cronA = new Cron($this->cache, 'php foo');
        $cronB = new Cron($this->cache, 'php foo');

        self::assertEquals(
            $cronA->daily()->hourly()->getExpression(),
            $cronB->hourly()->daily()->getExpression()
        );

        $cronA = new Cron($this->cache, 'php foo');
        $cronB = new Cron($this->cache, 'php foo');

        self::assertEquals(
            $cronA->weekdays()->hourly()->getExpression(),
            $cronB->hourly()->weekdays()->getExpression()
        );
    }

    public function testGetExpression()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * * *', $cron->getExpression());
    }

    public function testCron()
    {
        $cron = new Cron($this->cache, '');
        $cron->cron('*');

        self::assertSame('*', $cron->getExpression());
    }

    public function testHourly()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 * * * * *', $cron->hourly()->getExpression());
    }

    public function testDaily()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 0 * * * *', $cron->daily()->getExpression());
    }

    public function testMonthly()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 0 1 * * *', $cron->monthly()->getExpression());
    }

    public function testYearly()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 0 1 1 * *', $cron->yearly()->getExpression());
    }

    public function testQuarterly()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 0 1 */3 * *', $cron->quarterly()->getExpression());
    }

    public function testEveryMinute()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * * *', $cron->everyMinute()->getExpression());
    }

    public function testEveryFiveMinutes()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('*/5 * * * * *', $cron->everyFiveMinutes()->getExpression());
    }

    public function testEveryTenMinutes()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('*/10 * * * * *', $cron->everyTenMinutes()->getExpression());
    }

    public function testEveryThirtyMinutes()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0,30 * * * * *', $cron->everyThirtyMinutes()->getExpression());
    }

    public function testDays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 1 *', $cron->days(1)->getExpression());
    }

    public function testMonthlyOn()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 15 4 * * *', $cron->monthlyOn(4, '15:00')->getExpression());
    }

    public function testDailyAt()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('30 10 * * * *', $cron->dailyAt('10:30')->getExpression());
    }

    public function testTwiceDaily()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 1,13 * * * *', $cron->twiceDaily()->getExpression());
    }

    public function testWeekdays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 1-5 *', $cron->weekdays()->getExpression());
    }

    public function testMondays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 1 *', $cron->mondays()->getExpression());
    }

    public function testTuesdays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 2 *', $cron->tuesdays()->getExpression());
    }

    public function testWednesdays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 3 *', $cron->wednesdays()->getExpression());
    }

    public function testThursdays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 4 *', $cron->thursdays()->getExpression());
    }

    public function testFridays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 5 *', $cron->fridays()->getExpression());
    }

    public function testSaturdays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 6 *', $cron->saturdays()->getExpression());
    }

    public function testSundays()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('* * * * 0 *', $cron->sundays()->getExpression());
    }

    public function testWeekly()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 0 * * 0 *', $cron->weekly()->getExpression());
    }

    public function testWeeklyOn()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('0 0 * * 1 *', $cron->weeklyOn(1)->getExpression());
    }

    public function testBuildCommand()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $cron          = new Cron($this->cache, 'php -i');
        $defaultOutput = (DIRECTORY_SEPARATOR == '\\') ? 'NUL' : '/dev/null';

        self::assertSame("php -i > {$quote}{$defaultOutput}{$quote} 2>&1 &", $cron->buildCommand());
    }

    public function testGetAndSetUser()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame('root', $cron->setUser('root')->getUser());
    }

    public function testGetAndSetPath()
    {
        $cron = new Cron($this->cache, '');

        self::assertSame(__DIR__, $cron->setPath(__DIR__)->getPath());
    }

    public function testEnvironments()
    {
        $cron = new Cron($this->cache, '');

        $cron->setEnvironments(['dev', 'prod']);

        self::assertTrue($cron->runsInEnvironment('dev'));
    }

    public function testBuildCommandSendOutputTo()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $cron = new Cron($this->cache, 'php -i');
        $cron->sendOutputTo('/dev/null');

        self::assertSame("php -i > {$quote}/dev/null{$quote} 2>&1 &", $cron->buildCommand());

        $cron = new Cron($this->cache, 'php -i');
        $cron->sendOutputTo('/my folder/foo.log');

        self::assertSame("php -i > {$quote}/my folder/foo.log{$quote} 2>&1 &", $cron->buildCommand());
    }

    public function testBuildCommandAppendOutput()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $cron = new Cron($this->cache, 'php -i');
        $cron->appendOutputTo('/dev/null');

        self::assertSame("php -i >> {$quote}/dev/null{$quote} 2>&1 &", $cron->buildCommand());
    }

    public function testGetSummaryForDisplay()
    {
        $cron = new Cron($this->cache, 'php -i');

        self::assertSame($cron->buildCommand(), $cron->getSummaryForDisplay());

        $cron->setDescription('test');

        self::assertSame('test', $cron->getSummaryForDisplay());
    }

    public function testTimeBetweenChecks()
    {
        Chronos::setTestNow(Chronos::now()->startOfDay()->addHours(9));

        $cron = new Cron($this->cache, 'php foo');

        self::assertTrue($cron->between('8:00', '10:00')->filtersPass());
        self::assertTrue($cron->between('9:00', '9:00')->filtersPass());
        self::assertFalse($cron->between('10:00', '11:00')->filtersPass());
        self::assertFalse($cron->unlessBetween('8:00', '10:00')->filtersPass());
        self::assertTrue($cron->unlessBetween('10:00', '11:00')->isDue('test'));
    }

    public function testCronJobIsDueCheck()
    {
        Chronos::setTestNow(Chronos::create(2015, 1, 1, 0, 0, 0));

        $cron = new Cron($this->cache, 'php foo');

        self::assertEquals('* * * * 4 *', $cron->thursdays()->getExpression());
        self::assertTrue($cron->isDue('test'));

        // TODO fix test
        // $cron2 = new Cron($this->cache, 'php foo');
        // $cron2->wednesdays()->dailyAt('19:00');
        // $cron2->setTimezone('UTC');

        // self::assertEquals('0 19 * * 3 *', $cron2->getExpression());
        // self::assertTrue($cron2->isDue('test'));
    }

    public function testCronRun()
    {
        $_SERVER['test'] = false;

        $cron = new Cron($this->cache, 'php -i');

        $cron->before(function () {
            $_SERVER['test'] = 'before';
        });
        $cron->after(function () {
            $_SERVER['test'] = $_SERVER['test'] . ' after';
        });

        $cron->run();

        self::assertSame('before after', $_SERVER['test']);

        unset($_SERVER['test']);
    }

    public function testFrequencyMacro()
    {
        $cron = new Cron($this->cache, 'php foo');

        Cron::macro('everyXMinutes', function ($x) {
            return $this->spliceIntoPosition(1, "*/{$x}");
        });

        self::assertEquals('*/6 * * * * *', $cron->everyXMinutes(6)->getExpression());
    }
}
