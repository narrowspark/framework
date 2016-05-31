<?php
namespace Viserio\Events\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Events\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    const coreRequest   = 'core.request';
    const coreException = 'core.exception';
    const apiRequest    = 'api.request';
    const apiException  = 'api.exception';

    public function setup()
    {
        $this->dispatcher = new Dispatcher(new ArrayContainer([]));
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

    /**
     * Asserts the number of listeners added for a specific event or all events
     * in total.
     *
     * @param integer $expected
     * @param string  $eventName
     *
     * @return int
     */
    private function assertNumberListenersAdded($expected, $eventName = null): int
    {
        return $eventName !== null
            ? $this->assertEquals($expected, count($this->dispatcher->getListeners($eventName)))
            : $this->assertEquals($expected, array_sum(array_map('count', $this->dispatcher->getListeners())));
    }
}
