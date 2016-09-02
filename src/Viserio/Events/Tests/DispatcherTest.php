<?php
declare(strict_types=1);
namespace Viserio\Events\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Events\Dispatcher;
use Viserio\Events\Tests\Fixture\EventListener;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    const coreRequest = 'core.request';
    const coreException = 'core.exception';
    const apiRequest = 'api.request';
    const apiException = 'api.exception';

    private $dispatcher;
    private $listener;

    public function setup()
    {
        $this->dispatcher = new Dispatcher(new ArrayContainer([]));
        $this->listener = new EventListener();
    }

    public function testInitialState()
    {
        $ee = $this->dispatcher;

        $this->assertFalse($ee->hasListeners(self::coreRequest));
        $this->assertFalse($ee->hasListeners(self::coreException));
        $this->assertFalse($ee->hasListeners(self::apiRequest));
        $this->assertFalse($ee->hasListeners(self::apiException));
    }

    public function testListeners()
    {
        $ee = $this->dispatcher;

        $callback1 = function () {
        };
        $callback2 = function () {
        };

        $ee->attach('foo', $callback1, 100);
        $ee->attach('foo', $callback2, 200);

        $this->assertEquals([$callback2, $callback1], $ee->getListeners('foo'));
    }

    public function testHandleEvent()
    {
        $argResult = null;

        $ee = $this->dispatcher;

        $ee->attach('foo', function ($arg) use (&$argResult) {
            $argResult = $arg;
        });

        $this->assertTrue($ee->trigger('foo', ['bar']));
        $this->assertEquals('bar', $argResult);
    }

    /**
     * @depends testHandleEvent
     */
    public function testCancelEvent()
    {
        $argResult = 0;

        $ee = $this->dispatcher;
        $ee->attach('foo', function ($arg) use (&$argResult) {
            $argResult = 1;

            return false;
        });
        $ee->attach('foo', function ($arg) use (&$argResult) {
            $argResult = 2;
        });

        $this->assertFalse($ee->trigger('foo', ['bar']));
        $this->assertEquals(1, $argResult);
    }

    /**
     * @depends testCancelEvent
     */
    public function testPriority()
    {
        $argResult = 0;

        $ee = $this->dispatcher;
        $ee->attach('foo', function ($arg) use (&$argResult) {
            $argResult = 1;

            return false;
        });
        $ee->attach('foo', function ($arg) use (&$argResult) {
            $argResult = 2;

            return false;
        }, 1);

        $this->assertFalse($ee->trigger('foo', ['bar']));
        $this->assertEquals(2, $argResult);
    }

    /**
     * @depends testPriority
     */
    public function testPriority2()
    {
        $result = [];

        $ee = $this->dispatcher;
        $ee->attach('foo', function () use (&$result) {
            $result[] = 'a';
        }, 200);
        $ee->attach('foo', function () use (&$result) {
            $result[] = 'b';
        }, 50);
        $ee->attach('foo', function () use (&$result) {
            $result[] = 'c';
        }, 300);
        $ee->attach('foo', function () use (&$result) {
            $result[] = 'd';
        });
        $ee->trigger('foo');

        $this->assertEquals(['c', 'a', 'b', 'd'], $result);
    }

    public function testoff()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->attach('foo', $callBack);
        $ee->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertTrue($ee->detach('foo', $callBack));

        $ee->trigger('foo');

        $this->assertFalse($result);
    }

    public function testRemoveUnknownListener()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->attach('foo', $callBack);
        $ee->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertFalse($ee->detach('bar', $callBack));

        $ee->trigger('foo');

        $this->assertTrue($result);
    }

    public function testRemoveListenerTwice()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->attach('foo', $callBack);
        $ee->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertTrue($ee->detach('foo', $callBack));
        $this->assertFalse($ee->detach('foo', $callBack));

        $ee->trigger('foo');

        $this->assertFalse($result);
    }

    public function testRemoveAllListeners()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->attach('foo', $callBack);
        $ee->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $ee->removeAllListeners('foo');
        $ee->trigger('foo');

        $this->assertFalse($result);
    }

    public function testRemoveAllListenersNoArg()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->attach('foo', $callBack);
        $ee->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $ee->removeAllListeners();
        $ee->trigger('foo');

        $this->assertFalse($result);
    }

    public function testOnce()
    {
        $result = 0;

        $callBack = function () use (&$result) {
            ++$result;
        };

        $ee = $this->dispatcher;
        $ee->once('foo', $callBack);
        $ee->trigger('foo');
        $ee->trigger('foo');

        $this->assertEquals(1, $result);
    }

    /**
     * @depends testCancelEvent
     */
    public function testPriorityOnce()
    {
        $argResult = 0;

        $ee = $this->dispatcher;
        $ee->once('foo', function ($arg) use (&$argResult) {
            $argResult = 1;

            return false;
        });

        $ee->once('foo', function ($arg) use (&$argResult) {
            $argResult = 2;

            return false;
        }, 1);

        $this->assertFalse($ee->trigger('foo', ['bar']));

        $this->assertEquals(2, $argResult);
    }

    public function testRegisterSameListenerTwice()
    {
        $argResult = 0;

        $callback = function () use (&$argResult) {
            ++$argResult;
        };

        $ee = $this->dispatcher;

        $ee->attach('foo', $callback);
        $ee->attach('foo', $callback);
        $ee->trigger('foo');

        $this->assertEquals(2, $argResult);
    }

    public function testAddingAndRemovingWildcardListeners()
    {
        $ee = $this->dispatcher;

        $ee->attach('#', [$this->listener, 'onAny']);
        $ee->attach('core.*', [$this->listener, 'onCore']);
        $ee->attach('*.exception', [$this->listener, 'onException']);
        $ee->attach(self::coreRequest, [$this->listener, 'onCoreRequest']);

        $this->assertNumberListenersAdded(3, self::coreRequest);
        $this->assertNumberListenersAdded(3, self::coreException);
        $this->assertNumberListenersAdded(1, self::apiRequest);
        $this->assertNumberListenersAdded(2, self::apiException);

        $ee->detach('#', [$this->listener, 'onAny']);

        $this->assertNumberListenersAdded(2, self::coreRequest);
        $this->assertNumberListenersAdded(2, self::coreException);
        $this->assertNumberListenersAdded(0, self::apiRequest);
        $this->assertNumberListenersAdded(1, self::apiException);

        $ee->detach('core.*', [$this->listener, 'onCore']);

        $this->assertNumberListenersAdded(1, self::coreRequest);
        $this->assertNumberListenersAdded(1, self::coreException);
        $this->assertNumberListenersAdded(0, self::apiRequest);
        $this->assertNumberListenersAdded(1, self::apiException);

        $ee->detach('*.exception', [$this->listener, 'onException']);

        $this->assertNumberListenersAdded(1, self::coreRequest);
        $this->assertNumberListenersAdded(0, self::coreException);
        $this->assertNumberListenersAdded(0, self::apiRequest);
        $this->assertNumberListenersAdded(0, self::apiException);

        $ee->detach(self::coreRequest, [$this->listener, 'onCoreRequest']);

        $this->assertNumberListenersAdded(0, self::coreRequest);
        $this->assertNumberListenersAdded(0, self::coreException);
        $this->assertNumberListenersAdded(0, self::apiRequest);
        $this->assertNumberListenersAdded(0, self::apiException);
    }

    public function testAddedListenersWithWildcardsAreRegisteredLazily()
    {
        $ee = $this->dispatcher;

        $ee->attach('#', [$this->listener, 'onAny']);

        $this->assertTrue($ee->hasListeners(self::coreRequest));
        $this->assertNumberListenersAdded(1, self::coreRequest);

        $this->assertTrue($ee->hasListeners(self::coreException));
        $this->assertNumberListenersAdded(1, self::coreException);

        $this->assertTrue($ee->hasListeners(self::apiRequest));
        $this->assertNumberListenersAdded(1, self::apiRequest);

        $this->assertTrue($ee->hasListeners(self::apiException));
        $this->assertNumberListenersAdded(1, self::apiException);
    }

    public function testtrigger()
    {
        $ee = $this->dispatcher;

        $ee->attach('#', [$this->listener, 'onAny']);
        $ee->attach('core.*', [$this->listener, 'onCore']);
        $ee->attach('*.exception', [$this->listener, 'onException']);
        $ee->attach(self::coreRequest, [$this->listener, 'onCoreRequest']);

        $ee->trigger(self::coreRequest);
        $ee->trigger(self::coreException);
        $ee->trigger(self::apiRequest);
        $ee->trigger(self::apiException);

        $this->assertEquals(4, $this->listener->onAnyInvoked);
        $this->assertEquals(2, $this->listener->onCoreInvoked);
        $this->assertEquals(1, $this->listener->onCoreRequestInvoked);
        $this->assertEquals(2, $this->listener->onExceptionInvoked);
    }

    public function testLazyListenerInitializatiattach()
    {
        $listenerProviderInvoked = 0;

        $listenerProvider = function () use (&$listenerProviderInvoked) {
            ++$listenerProviderInvoked;

            return 'callback';
        };

        $ee = new Dispatcher(new ArrayContainer([]));
        $ee->attach('foo', $listenerProvider);

        $this->assertEquals(
            0,
            $listenerProviderInvoked,
            'The listener provider should not be invoked until the listener is requested'
        );

        $ee->trigger('foo');

        $this->assertEquals([$listenerProvider], $ee->getListeners('foo'));
        $this->assertEquals(
            1,
            $listenerProviderInvoked,
            'The listener provider should be invoked when the listener is requested'
        );

        $this->assertEquals([$listenerProvider], $ee->getListeners('foo'));
        $this->assertEquals(1, $listenerProviderInvoked, 'The listener provider should only be invoked once');
    }

    /**
     * Asserts the number of listeners added for a specific event or all events
     * in total.
     *
     * @param int    $expected
     * @param string $eventName
     */
    private function assertNumberListenersAdded(int $expected, string $eventName)
    {
        $ee = $this->dispatcher;

        return $this->assertEquals($expected, count($ee->getListeners($eventName)));
    }
}
