<?php
namespace Viserio\Events\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Events\Dispatcher;

class DispatcherTest extends \PHPUnit_Framework_TestCase
{
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

        $ee->on('foo', $callback1, 200);
        $ee->on('foo', $callback2, 100);

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

        $this->assertFalse(
            $ee->emit('foo', ['bar'])
        );

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

        $this->assertFalse(
            $ee->emit('foo', ['bar'])
        );

        $this->assertEquals(2, $argResult);
    }

    /**
     * @depends testPriority
     */
    public function testPriority2()
    {
        $result = [];

        $ee = $this->dispatcher;

        $ee->on('foo', function () use (&$result) {
            $result[] = 'a';
        }, 200);

        $ee->on('foo', function () use (&$result) {
            $result[] = 'b';
        }, 50);

        $ee->on('foo', function () use (&$result) {
            $result[] = 'c';
        }, 300);

        $ee->on('foo', function () use (&$result) {
            $result[] = 'd';
        });

        $ee->emit('foo');

        $this->assertEquals(['b', 'd', 'a', 'c'], $result);
    }

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

        $this->assertTrue(
            $ee->off('foo', $callBack)
        );

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

        $this->assertTrue(
            $ee->off('foo', $callBack)
        );

        $this->assertFalse(
            $ee->off('foo', $callBack)
        );

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

        $this->assertFalse(
            $ee->emit('foo', ['bar'])
        );

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

    public function testWildcardListeners()
    {
        $ee = $this->dispatcher;

        $callback1 = function () {
        };
        $callback2 = function () {
        };
        $callback3 = function () {
        };

        $ee->on('foo.*', $callback1);
        $ee->on('foo.bar', $callback2);
        $ee->on('foo.qux', $callback3);

        $this->assertEquals([$callback1, $callback2], $ee->getListeners('foo.bar'));
    }

    public function testWildcardCalls()
    {
        $argResult = 0;

        $ee = $this->dispatcher;

        $ee->on('foo.*', function () use (&$argResult) {
            ++$argResult;
        });

        $ee->on('foo.bar', function () use (&$argResult) {
            ++$argResult;
        });

        $ee->emit('foo.bar');
        $ee->emit('foo.bar');
        $ee->emit('foo.qux');

        $this->assertEquals(5, $argResult);
    }

    public function testWildcardListenersRespectPriority()
    {
        $result = [];

        $ee = $this->dispatcher;

        $ee->on('foo.*', function () use (&$result) {
            $result[] = 'a';
        }, 30);

        $ee->on('foo.bar', function () use (&$result) {
            $result[] = 'b';
        }, 10);

        $ee->on('foo.bar', function () use (&$result) {
            $result[] = 'c';
        }, 20);

        $ee->emit('foo.bar');

        $this->assertEquals(['b', 'c', 'a'], $result);
    }

    public function testGlobalWildcard()
    {
        $result = false;

        $ee = $this->dispatcher;

        $ee->on('*', function () use (&$result) {
            $result = true;
        });

        $ee->emit('foo');

        $this->assertTrue($result);
    }

    public function testUseWildcardToRegisterGlobalListener()
    {
        $fooSpy = 0;
        $barSpy = 0;
        $quxSpy = 0;

        $ee = $this->dispatcher;

        $ee->on('*', function () use (&$fooSpy, &$barSpy, &$quxSpy) {
            ++$fooSpy;
            ++$barSpy;
            ++$quxSpy;
        });

        $ee->on('foo', function () use (&$fooSpy) {
            ++$fooSpy;
        });

        $ee->on('bar', function () use (&$barSpy) {
            ++$barSpy;
        });

        $ee->emit('foo');
        $ee->emit('bar');
        $ee->emit('qux');

        $this->assertEquals(4, $fooSpy);
        $this->assertEquals(4, $barSpy);
        $this->assertEquals(3, $quxSpy);
    }
}
