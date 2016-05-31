<?php
namespace Viserio\Events\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Events\Dispatcher;
use Viserio\Events\Tests\Fixture\EventListener;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    const coreRequest   = 'core.request';
    const coreException = 'core.exception';
    const apiRequest    = 'api.request';
    const apiException  = 'api.exception';

    private $dispatcher;
    private $listener;

    public function setup()
    {
        $this->dispatcher = new Dispatcher(new ArrayContainer([]));
        $this->listener   = new EventListener();
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

        $ee->on('foo', $callback1, 100);
        $ee->on('foo', $callback2, 200);

        $this->assertEquals([$callback2, $callback1], $ee->getListeners('foo'));
    }

    public function testHandleEvent()
    {
        $argResult = null;

        $ee = $this->dispatcher;

        $ee->on('foo', function ($arg) use (&$argResult) {
            $argResult = $arg;
        });

        $this->assertTrue($ee->emit('foo', ['bar']));
        $this->assertEquals('bar', $argResult);
    }

    /**
     * @depends testHandleEvent
     */
    public function testCancelEvent()
    {
        $argResult = 0;

        $ee = $this->dispatcher;
        $ee->on('foo', function ($arg) use (&$argResult) {
            $argResult = 1;

            return false;
        });
        $ee->on('foo', function ($arg) use (&$argResult) {
            $argResult = 2;
        });

        $this->assertFalse($ee->emit('foo', ['bar']));
        $this->assertEquals(1, $argResult);
    }

    /**
     * @depends testCancelEvent
     */
    public function testPriority()
    {
        $argResult = 0;

        $ee = $this->dispatcher;
        $ee->on('foo', function ($arg) use (&$argResult) {
            $argResult = 1;

            return false;
        });
        $ee->on('foo', function ($arg) use (&$argResult) {
            $argResult = 2;

            return false;
        }, 1);

        $this->assertFalse($ee->emit('foo', ['bar']));
        $this->assertEquals(2, $argResult);
    }

    // /**
    //  * @depends testPriority
    //  */
    // public function testPriority2()
    // {
    //     $result = [];

    //     $ee = $this->dispatcher;

    //     $ee->on('foo', function () use (&$result) {
    //         $result[] = 'a';
    //     }, 200);

    //     $ee->on('foo', function () use (&$result) {
    //         $result[] = 'b';
    //     }, 50);

    //     $ee->on('foo', function () use (&$result) {
    //         $result[] = 'c';
    //     }, 300);

    //     $ee->on('foo', function () use (&$result) {
    //         $result[] = 'd';
    //     });

    //     $ee->emit('foo');

    //     $this->assertEquals(['b', 'd', 'a', 'c'], $result);
    // }

    public function testoff()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->on('foo', $callBack);
        $ee->emit('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertTrue($ee->off('foo', $callBack));

        $ee->emit('foo');

        $this->assertFalse($result);
    }

    public function testRemoveUnknownListener()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->on('foo', $callBack);
        $ee->emit('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertFalse($ee->off('bar', $callBack));

        $ee->emit('foo');

        $this->assertTrue($result);
    }

    public function testRemoveListenerTwice()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->on('foo', $callBack);
        $ee->emit('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertTrue($ee->off('foo', $callBack));
        $this->assertFalse($ee->off('foo', $callBack));

        $ee->emit('foo');

        $this->assertFalse($result);
    }

    public function testRemoveAllListeners()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->on('foo', $callBack);
        $ee->emit('foo');

        $this->assertTrue($result);

        $result = false;

        $ee->removeAllListeners('foo');
        $ee->emit('foo');

        $this->assertFalse($result);
    }

    public function testRemoveAllListenersNoArg()
    {
        $result = false;

        $callBack = function () use (&$result) {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->on('foo', $callBack);
        $ee->emit('foo');

        $this->assertTrue($result);

        $result = false;

        $ee->removeAllListeners();
        $ee->emit('foo');

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
        $ee->emit('foo');
        $ee->emit('foo');

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

        $this->assertFalse($ee->emit('foo', ['bar']));

        $this->assertEquals(2, $argResult);
    }

    public function testRegisterSameListenerTwice()
    {
        $argResult = 0;

        $callback = function () use (&$argResult) {
            ++$argResult;
        };

        $ee = $this->dispatcher;

        $ee->on('foo', $callback);
        $ee->on('foo', $callback);
        $ee->emit('foo');

        $this->assertEquals(2, $argResult);
    }

    public function testAddingAndRemovingWildcardListeners()
    {
        $ee = $this->dispatcher;

        $ee->on('#', [$this->listener, 'onAny']);
        $ee->on('core.*', [$this->listener, 'onCore']);
        $ee->on('*.exception', [$this->listener, 'onException']);
        $ee->on(self::coreRequest, [$this->listener, 'onCoreRequest']);

        $this->assertNumberListenersAdded(3, self::coreRequest);
        $this->assertNumberListenersAdded(3, self::coreException);
        $this->assertNumberListenersAdded(1, self::apiRequest);
        $this->assertNumberListenersAdded(2, self::apiException);

        $ee->removeListener('#', [$this->listener, 'onAny']);

        $this->assertNumberListenersAdded(2, self::coreRequest);
        $this->assertNumberListenersAdded(2, self::coreException);
        $this->assertNumberListenersAdded(0, self::apiRequest);
        $this->assertNumberListenersAdded(1, self::apiException);

        $ee->removeListener('core.*', [$this->listener, 'onCore']);

        $this->assertNumberListenersAdded(1, self::coreRequest);
        $this->assertNumberListenersAdded(1, self::coreException);
        $this->assertNumberListenersAdded(0, self::apiRequest);
        $this->assertNumberListenersAdded(1, self::apiException);

        $ee->removeListener('*.exception', [$this->listener, 'onException']);

        $this->assertNumberListenersAdded(1, self::coreRequest);
        $this->assertNumberListenersAdded(0, self::coreException);
        $this->assertNumberListenersAdded(0, self::apiRequest);
        $this->assertNumberListenersAdded(0, self::apiException);

        $ee->removeListener(self::coreRequest, [$this->listener, 'onCoreRequest']);

        $this->assertNumberListenersAdded(0, self::coreRequest);
        $this->assertNumberListenersAdded(0, self::coreException);
        $this->assertNumberListenersAdded(0, self::apiRequest);
        $this->assertNumberListenersAdded(0, self::apiException);
    }

    public function testAddedListenersWithWildcardsAreRegisteredLazily()
    {
        $ee = $this->dispatcher;

        $ee->on('#', [$this->listener, 'onAny']);

        $this->assertTrue($ee->hasListeners(self::coreRequest));
        $this->assertNumberListenersAdded(1, self::coreRequest);

        $this->assertTrue($ee->hasListeners(self::coreException));
        $this->assertNumberListenersAdded(1, self::coreException);

        $this->assertTrue($ee->hasListeners(self::apiRequest));
        $this->assertNumberListenersAdded(1, self::apiRequest);

        $this->assertTrue($ee->hasListeners(self::apiException));
        $this->assertNumberListenersAdded(1, self::apiException);
    }

    public function testDispatch()
    {
        $ee = $this->dispatcher;

        $ee->on('#', [$this->listener, 'onAny']);
        $ee->on('core.*', [$this->listener, 'onCore']);
        $ee->on('*.exception', [$this->listener, 'onException']);
        $ee->on(self::coreRequest, [$this->listener, 'onCoreRequest']);

        $ee->dispatch(self::coreRequest);
        $ee->dispatch(self::coreException);
        $ee->dispatch(self::apiRequest);
        $ee->dispatch(self::apiException);

        $this->assertEquals(4, $this->listener->onAnyInvoked);
        $this->assertEquals(2, $this->listener->onCoreInvoked);
        $this->assertEquals(1, $this->listener->onCoreRequestInvoked);
        $this->assertEquals(2, $this->listener->onExceptionInvoked);
    }

    /**
     * Asserts the number of listeners added for a specific event or all events
     * in total.
     *
     * @param int    $expected
     * @param string $eventName
     *
     * @return int
     */
    private function assertNumberListenersAdded($expected, $eventName = null): int
    {
        $ee = $this->dispatcher;

        return $eventName !== null
            ? $this->assertEquals($expected, count($ee->getListeners($eventName)))
            : $this->assertEquals($expected, array_sum(array_map('count', $ee->getListeners())));
    }
}
