<?php
declare(strict_types=1);
namespace Viserio\Cron\Tests;

use Viserio\Cron\CallbackCron;

class CallbackCronTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid scheduled callback cron job. Must be string or callable.
     */
    public function testCallbackCronToThrowException()
    {
        new CallbackCron(new CallbackCron('tests'));
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage A scheduled cron job description is required to prevent overlapping. Use the 'description' method before 'withoutOverlapping'.
     */
    public function testWithoutOverlappingToThrowException()
    {
        $cron = new CallbackCron('tests');
        $cron->withoutOverlapping();
    }

    public function testBasicCronCompilation()
    {
        $_SERVER['test'] = false;

        $cron = new CallbackCron(function () {
            $_SERVER['test'] = true;
        });

        $cron->run();

        $this->assertTrue($_SERVER['test']);

        unset($_SERVER['test']);

        $_SERVER['test'] = false;

        $cron = new CallbackCron(function () {
            $_SERVER['test'] = true;
        });

        $cron->setDescription('run test')->run();

        $this->assertTrue($_SERVER['test']);
        $this->assertSame('run test', $cron->getSummaryForDisplay());

        unset($_SERVER['test']);
    }
}
