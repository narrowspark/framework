<?php
declare(strict_types=1);
namespace Viserio\Events\Tests;

use Viserio\Events\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Event
     */
    private $object;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        $this->event = new Event('test', $this, ['invoker' => $this]);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        $this->event = null;
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Event name cant be empty.
     */
    public function testSetName()
    {
        new Event('', $this, ['invoker' => $this]);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The event name must only contain the characters A-Z, a-z, 0-9, _, and '.'.
     */
    public function testSetNameWithInvalidName()
    {
        new Event('te-st', $this, ['invoker' => $this]);
    }

    public function testGetName()
    {
        $this->assertSame('test', $this->event->getName());
    }

    public function testGetTarget()
    {
        $this->assertEquals($this, $this->event->getTarget());
    }

    public function testGetParams()
    {
        $p = $this->event->getParams();

        $this->assertArrayHasKey('invoker', $p);
    }

    public function testStopPropagation()
    {
        $this->assertFalse($this->event->isPropagationStopped());

        $this->event->stopPropagation();

        $this->assertTrue($this->event->isPropagationStopped());
    }
}
