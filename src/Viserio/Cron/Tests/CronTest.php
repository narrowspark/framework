<?php
declare(strict_types=1);
namespace Viserio\Cron\Tests;

use Cake\Chronos\Chronos;
use Viserio\Cron\Cron;

class CronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The default configuration timezone.
     *
     * @var string
     */
    protected $defaultTimezone;

    public function setUp()
    {
        $this->defaultTimezone = date_default_timezone_get();

        date_default_timezone_set('UTC');
    }

    public function tearDown()
    {
        date_default_timezone_set($this->defaultTimezone);

        Chronos::setTestNow(null);
    }

    public function testBasicCronCompilation()
    {
        $cron = new Cron('php foo');

        $this->assertEquals('* * * * * *', $cron->getExpression());
        $this->assertTrue($cron->isDue('test'));
        $this->assertTrue($cron->skip(function () {
            return true;
        })->isDue('test'));
        $this->assertFalse($cron->skip(function () {
            return true;
        })->filtersPass());

        $cron = new Cron('php foo');

        $this->assertEquals('* * * * * *', $cron->getExpression());
        $this->assertFalse($cron->setEnvironments('local')->isDue('test'));

        $cron = new Cron('php foo');

        $this->assertEquals('* * * * * *', $cron->getExpression());
        $this->assertFalse($cron->when(function () {
            return false;
        })->filtersPass());
    }

    public function testCronChainedRulesShouldBeCommutative()
    {
        $cronA = new Cron('php foo');
        $cronB = new Cron('php foo');

        $this->assertEquals(
            $cronA->daily()->hourly()->getExpression(),
            $cronB->hourly()->daily()->getExpression()
        );

        $cronA = new Cron('php foo');
        $cronB = new Cron('php foo');

        $this->assertEquals(
            $cronA->weekdays()->hourly()->getExpression(),
            $cronB->hourly()->weekdays()->getExpression()
        );
    }

    public function testGetExpression()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * * *', $cron->getExpression());
    }

    public function testCron()
    {
        $cron = new Cron('');
        $cron->cron('*');

        $this->assertSame('*', $cron->getExpression());
    }

    public function testHourly()
    {
        $cron = new Cron('');

        $this->assertSame('0 * * * * *', $cron->hourly()->getExpression());
    }

    public function testDaily()
    {
        $cron = new Cron('');

        $this->assertSame('0 0 * * * *', $cron->daily()->getExpression());
    }

    public function testMonthly()
    {
        $cron = new Cron('');

        $this->assertSame('0 0 1 * * *', $cron->monthly()->getExpression());
    }

    public function testYearly()
    {
        $cron = new Cron('');

        $this->assertSame('0 0 1 1 * *', $cron->yearly()->getExpression());
    }

    public function testQuarterly()
    {
        $cron = new Cron('');

        $this->assertSame('0 0 1 */3 * *', $cron->quarterly()->getExpression());
    }

    public function testEveryMinute()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * * *', $cron->everyMinute()->getExpression());
    }

    public function testEveryFiveMinutes()
    {
        $cron = new Cron('');

        $this->assertSame('*/5 * * * * *', $cron->everyFiveMinutes()->getExpression());
    }

    public function testEveryTenMinutes()
    {
        $cron = new Cron('');

        $this->assertSame('*/10 * * * * *', $cron->everyTenMinutes()->getExpression());
    }

    public function testEveryThirtyMinutes()
    {
        $cron = new Cron('');

        $this->assertSame('0,30 * * * * *', $cron->everyThirtyMinutes()->getExpression());
    }

    public function testDays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 1 *', $cron->days(1)->getExpression());
    }

    public function testMonthlyOn()
    {
        $cron = new Cron('');

        $this->assertSame('0 15 4 * * *', $cron->monthlyOn(4, '15:00')->getExpression());
    }

    public function testDailyAt()
    {
        $cron = new Cron('');

        $this->assertSame('30 10 * * * *', $cron->dailyAt('10:30')->getExpression());
    }

    public function testTwiceDaily()
    {
        $cron = new Cron('');

        $this->assertSame('0 1,13 * * * *', $cron->twiceDaily()->getExpression());
    }

    public function testWeekdays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 1-5 *', $cron->weekdays()->getExpression());
    }

    public function testMondays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 1 *', $cron->mondays()->getExpression());
    }

    public function testTuesdays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 2 *', $cron->tuesdays()->getExpression());
    }

    public function testWednesdays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 3 *', $cron->wednesdays()->getExpression());
    }

    public function testThursdays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 4 *', $cron->thursdays()->getExpression());
    }

    public function testFridays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 5 *', $cron->fridays()->getExpression());
    }

    public function testSaturdays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 6 *', $cron->saturdays()->getExpression());
    }

    public function testSundays()
    {
        $cron = new Cron('');

        $this->assertSame('* * * * 0 *', $cron->sundays()->getExpression());
    }

    public function testWeekly()
    {
        $cron = new Cron('');

        $this->assertSame('0 0 * * 0 *', $cron->weekly()->getExpression());
    }

    public function testWeeklyOn()
    {
        $cron = new Cron('');

        $this->assertSame('0 0 * * 1 *', $cron->weeklyOn(1)->getExpression());
    }

    public function testBuildCommand()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $cron = new Cron('php -i');
        $defaultOutput = (DIRECTORY_SEPARATOR == '\\') ? 'NUL' : '/dev/null';

        $this->assertSame("php -i > {$quote}{$defaultOutput}{$quote} 2>&1 &", $cron->buildCommand());
    }

    public function testGetAndSetUser()
    {
        $cron = new Cron('');

        $this->assertSame('root', $cron->setUser('root')->getUser());
    }

    public function testGetAndSetPath()
    {
        $cron = new Cron('');

        $this->assertSame(__DIR__, $cron->setPath(__DIR__)->getPath());
    }

    public function testEnvironments()
    {
        $cron = new Cron('');

        $cron->setEnvironments(['dev', 'prod']);

        $this->assertTrue($cron->runsInEnvironment('dev'));
    }

    public function testBuildCommandSendOutputTo()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $cron = new Cron('php -i');
        $cron->sendOutputTo('/dev/null');

        $this->assertSame("php -i > {$quote}/dev/null{$quote} 2>&1 &", $cron->buildCommand());

        $cron = new Cron('php -i');
        $cron->sendOutputTo('/my folder/foo.log');

        $this->assertSame("php -i > {$quote}/my folder/foo.log{$quote} 2>&1 &", $cron->buildCommand());
    }

    public function testBuildCommandAppendOutput()
    {
        $quote = (DIRECTORY_SEPARATOR == '\\') ? '"' : "'";

        $cron = new Cron('php -i');
        $cron->appendOutputTo('/dev/null');

        $this->assertSame("php -i >> {$quote}/dev/null{$quote} 2>&1 &", $cron->buildCommand());
    }

    public function testGetSummaryForDisplay()
    {
        $cron = new Cron('php -i');

        $this->assertSame($cron->buildCommand(), $cron->getSummaryForDisplay());

        $cron->setDescription('test');

        $this->assertSame('test', $cron->getSummaryForDisplay());
    }

    public function testTimeBetweenChecks()
    {
        Chronos::setTestNow(Chronos::now()->startOfDay()->addHours(9));

        $cron = new Cron('php foo');

        $this->assertTrue($cron->between('8:00', '10:00')->filtersPass());
        $this->assertTrue($cron->between('9:00', '9:00')->filtersPass());
        $this->assertFalse($cron->between('10:00', '11:00')->filtersPass());
        $this->assertFalse($cron->unlessBetween('8:00', '10:00')->filtersPass());
        $this->assertTrue($cron->unlessBetween('10:00', '11:00')->isDue('test'));
    }

    public function testCronJobIsDueCheck()
    {
        Chronos::setTestNow(Chronos::create(2015, 1, 1, 0, 0, 0));

        $cron = new Cron('php foo');

        $this->assertEquals('* * * * 4 *', $cron->thursdays()->getExpression());
        $this->assertTrue($cron->isDue('test'));

        $cron = new Cron('php foo');
        $cron->wednesdays()->dailyAt('19:00')->setTimezone('EST');

        $this->assertEquals('0 19 * * 3 *', $cron->getExpression());
        #$this->assertTrue($cron->isDue('test')); //TODO
    }
}
