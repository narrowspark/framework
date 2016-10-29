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
     * @expectedExceptionMessage A scheduled cron job name is required to prevent overlapping. Use the 'name' method before 'withoutOverlapping'.
     */
    public function testWithoutOverlappingToThrowException()
    {
        $cron = new CallbackCron('tests');
        $cron->withoutOverlapping();
    }
}
