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

    public function testSetName()
    {
        $newname = 'newname';

        $this->event->setName($newname);

        $this->assertTrue($newname === $this->event->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetName2()
    {
        $newname = '';

        $this->event->setName($newname);

        $this->assertTrue($newname === $this->event->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetName3()
    {
        $newname = 100;

        $this->event->setName($newname);

        $this->assertTrue($newname === $this->event->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNameWithInvalidName()
    {
        $this->event->setName('te-st');
    }

    public function testGetName()
    {
        $this->assertTrue('test' === $this->event->getName());
    }

    public function testSetTarget1()
    {
        // Target is an object
        $this->assertTrue($this === $this->event->getTarget());

        // set Target to class name
        $this->event->setTarget(get_class($this->event));

        // test class
        $this->assertEquals(
            get_class($this->event),
            $this->event->getTarget()
        );
    }

    public function testGetTarget()
    {
        $this->event->setTarget(get_class($this->event));

        $this->assertTrue(
            get_class($this->event) === $this->event->getTarget()
        );
    }

    public function testGetParam1()
    {
        $this->assertTrue($this === $this->event->getParam('invoker'));
        $this->assertTrue(null === $this->event->getParam('wow'));
    }

    public function testGetParams()
    {
        $p = $this->event->getParams();

        $this->assertArrayHasKey('invoker', $p);
    }

    public function testSetParams()
    {
        $a = ['a' => 'aa', 'b' => 'bb'];

        $this->event->setParams($a);

        $this->assertTrue($a === $this->event->getParams());
    }

    public function testStopPropagation()
    {
        $this->assertTrue(false === $this->event->isPropagationStopped());

        $this->event->stopPropagation(true);

        $this->assertTrue(true === $this->event->isPropagationStopped());
    }
}
