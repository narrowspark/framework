<?php
namespace Viserio\Events\Tests;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Events\Dispatcher;
use Viserio\Events\Tests\Fixture\FooService;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->container = new ArrayContainer([
            'foo.service' => function () {
                return new FooService();
            }
        ]);

        $this->dispatcher = new Dispatcher(new EventDispatcher(), $this->container);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddListenerServiceThrowsIfCallbackNotArray()
    {
        $this->dispatcher->addListenerService('foo', 'onBar');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddListenerServiceThrowsIfCallbackWrongSize()
    {
        $this->dispatcher->addListenerService('foo', ['onBar']);
    }

    public function testAddListener()
    {
        $this->dispatcher->addListener('foo', [$this->container->get('foo.service'), 'onFoo']);
        $this->dispatcher->dispatch('foo', new Event());

        $this->assertEquals('foo', $this->container->get('foo.service')->string);
    }

    public function testAddListenerService()
    {
        $this->dispatcher->addListenerService('foo', ['foo.service', 'onFoo'], 5);
        $this->dispatcher->addListenerService('foo', ['foo.service', 'onBar1']);
        $this->dispatcher->dispatch('foo', new Event());

        $this->assertEquals('foobar1', $this->container->get('foo.service')->string);
    }

    public function testRemoveListener()
    {
        $this->dispatcher->addListenerService('foo', ['foo.service', 'onFoo'], 5);
        $this->dispatcher->addListenerService('foo', ['foo.service', 'onBar1']);
        $this->dispatcher->removeListener('foo', ['foo.service', 'onFoo']);
        $this->dispatcher->dispatch('foo', new Event());

        $this->assertEquals('bar1', $this->container->get('foo.service')->string);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddSubscriberThrowsIfClassNotImplementEventSubscriberInterface()
    {
        $this->dispatcher->addSubscriberService('foo.service', 'stdClass');
    }

    public function testAddSubscriberService()
    {
        $this->dispatcher->addSubscriberService('foo.service', 'Viserio\Events\Test\FooService');
        $this->dispatcher->dispatch('foo', new Event());
        $this->assertEquals('foo', $this->container->get('foo.service')->string);

        $this->dispatcher->dispatch('bar', new Event());
        $this->assertEquals('foobar2bar1', $this->container->get('foo.service')->string);

        $this->dispatcher->dispatch('buzz', new Event());
        $this->assertEquals('foobar2bar1buzz', $this->container->get('foo.service')->string);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRemoveSubscriberThrowsIfClassNotImplementEventSubscriberInterface()
    {
        $this->dispatcher->removeSubscriberService('foo.service', 'stdClass');
    }

    public function testRemoveSubscriberService()
    {
        $this->dispatcher->addSubscriberService('foo.service', 'Viserio\Events\Test\FooService');
        $this->assertTrue($this->dispatcher->hasListeners('foo'));
        $this->assertTrue($this->dispatcher->hasListeners('bar'));
        $this->assertTrue($this->dispatcher->hasListeners('buzz'));

        $this->dispatcher->removeSubscriberService('foo.service', 'Viserio\Events\Test\FooService');
        $this->assertFalse($this->dispatcher->hasListeners('foo'));
        $this->assertFalse($this->dispatcher->hasListeners('bar'));
        $this->assertFalse($this->dispatcher->hasListeners('buzz'));
    }

    public function testAddSubscriber()
    {
        $this->dispatcher->addSubscriber($this->container->get('foo.service'));
        $this->dispatcher->dispatch('foo', new Event());
        $this->assertEquals('foo', $this->container->get('foo.service')->string);

        $this->dispatcher->dispatch('bar', new Event());
        $this->assertEquals('foobar2bar1', $this->container->get('foo.service')->string);

        $this->dispatcher->dispatch('buzz', new Event());
        $this->assertEquals('foobar2bar1buzz', $this->container->get('foo.service')->string);
    }

    public function testRemoveSubscriber()
    {
        $this->dispatcher->addSubscriber($this->container->get('foo.service'));
        $this->assertTrue($this->dispatcher->hasListeners('foo'));
        $this->assertTrue($this->dispatcher->hasListeners('bar'));
        $this->assertTrue($this->dispatcher->hasListeners('buzz'));

        $this->dispatcher->removeSubscriber($this->container->get('foo.service'));
        $this->assertFalse($this->dispatcher->hasListeners('foo'));
        $this->assertFalse($this->dispatcher->hasListeners('bar'));
        $this->assertFalse($this->dispatcher->hasListeners('buzz'));
    }

    public function testGetListeners()
    {
        $this->dispatcher->addSubscriberService('foo.service', 'Viserio\Events\Test\FooService');
        $this->assertEquals(2, count($this->dispatcher->getListeners('bar')));
        $this->assertEquals(3, count($this->dispatcher->getListeners()));
    }
}
