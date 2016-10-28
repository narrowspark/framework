<?php
declare(strict_types=1);
namespace Viserio\Cron\Tests;

use Viserio\Cron\Cron;

class CronTest extends \PHPUnit_Framework_TestCase
{
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

        $this->assertSame('0 0 1 * * *', $cron->monthlyOn()->getExpression());
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

    public function testBetween()
    {
        $cron = new Cron('');
        $cron->between('10:00', '12:00');
    }

    public function testUnlessBetween()
    {
    }

    public function testWhen()
    {
    }

    public function testSkip()
    {
    }

    public function testBefore()
    {
    }

    public function testAfter()
    {
    }

    public function testDescription()
    {
    }

    public function testTimezone()
    {
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
}
