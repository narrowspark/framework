<?php

namespace Brainwave\Events\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.8-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Container\Container;
use Brainwave\Events\Dispatcher;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * FilesystemTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class BrainwaveTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->container = new Container();
        $this->container['foo.service'] = function () {
            return new FooService();
        };

        $dispatcher = new EventDispatcher();

        $this->dispatcher = new Dispatcher($dispatcher, $this->container);
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
        $this->dispatcher->addListener('foo', [$this->container['foo.service'], 'onFoo']);
        $this->dispatcher->dispatch('foo', new Event());
        $this->assertEquals('foo', $this->container['foo.service']->string);
    }

    public function testAddListenerService()
    {
        $this->dispatcher->addListenerService('foo', ['foo.service', 'onFoo'], 5);
        $this->dispatcher->addListenerService('foo', ['foo.service', 'onBar1']);
        $this->dispatcher->dispatch('foo', new Event());
        $this->assertEquals('foobar1', $this->container['foo.service']->string);
    }

    public function testRemoveListener()
    {
        $this->dispatcher->addListenerService('foo', ['foo.service', 'onFoo'], 5);
        $this->dispatcher->addListenerService('foo', ['foo.service', 'onBar1']);
        $this->dispatcher->removeListener('foo', ['foo.service', 'onFoo']);
        $this->dispatcher->dispatch('foo', new Event());
        $this->assertEquals('bar1', $this->container['foo.service']->string);
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
        $this->dispatcher->addSubscriberService('foo.service', 'Brainwave\Events\Test\FooService');
        $this->dispatcher->dispatch('foo', new Event());
        $this->assertEquals('foo', $this->container['foo.service']->string);
        $this->dispatcher->dispatch('bar', new Event());
        $this->assertEquals('foobar2bar1', $this->container['foo.service']->string);
        $this->dispatcher->dispatch('buzz', new Event());
        $this->assertEquals('foobar2bar1buzz', $this->container['foo.service']->string);
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
        $this->dispatcher->addSubscriberService('foo.service', 'Brainwave\Events\Test\FooService');
        $this->assertTrue($this->dispatcher->hasListeners('foo'));
        $this->assertTrue($this->dispatcher->hasListeners('bar'));
        $this->assertTrue($this->dispatcher->hasListeners('buzz'));
        $this->dispatcher->removeSubscriberService('foo.service', 'Brainwave\Events\Test\FooService');
        $this->assertFalse($this->dispatcher->hasListeners('foo'));
        $this->assertFalse($this->dispatcher->hasListeners('bar'));
        $this->assertFalse($this->dispatcher->hasListeners('buzz'));
    }

    public function testAddSubscriber()
    {
        $this->dispatcher->addSubscriber($this->container['foo.service']);
        $this->dispatcher->dispatch('foo', new Event());
        $this->assertEquals('foo', $this->container['foo.service']->string);
        $this->dispatcher->dispatch('bar', new Event());
        $this->assertEquals('foobar2bar1', $this->container['foo.service']->string);
        $this->dispatcher->dispatch('buzz', new Event());
        $this->assertEquals('foobar2bar1buzz', $this->container['foo.service']->string);
    }

    public function testRemoveSubscriber()
    {
        $this->dispatcher->addSubscriber($this->container['foo.service']);
        $this->assertTrue($this->dispatcher->hasListeners('foo'));
        $this->assertTrue($this->dispatcher->hasListeners('bar'));
        $this->assertTrue($this->dispatcher->hasListeners('buzz'));
        $this->dispatcher->removeSubscriber($this->container['foo.service']);
        $this->assertFalse($this->dispatcher->hasListeners('foo'));
        $this->assertFalse($this->dispatcher->hasListeners('bar'));
        $this->assertFalse($this->dispatcher->hasListeners('buzz'));
    }

    public function testGetListeners()
    {
        $this->dispatcher->addSubscriberService('foo.service', 'Brainwave\Events\Test\FooService');
        $this->assertEquals(2, count($this->dispatcher->getListeners('bar')));
        $this->assertEquals(3, count($this->dispatcher->getListeners()));
    }
}

class FooService implements EventSubscriberInterface
{
    public $string = '';

    public function onFoo(Event $e)
    {
        $this->string .= 'foo';
    }

    public function onBar1(Event $e)
    {
        $this->string .= 'bar1';
    }

    public function onBar2(Event $e)
    {
        $this->string .= 'bar2';
    }

    public function onBuzz(Event $e)
    {
        $this->string .= 'buzz';
    }

    public static function getSubscribedEvents()
    {
        return [
            'foo' => 'onFoo',
            'bar' => [
                ['onBar1'],
                ['onBar2', 10],
            ],
            'buzz' => ['onBuzz', 5],
        ];
    }
}
