<?php
declare(strict_types=1);
namespace Viserio\Events\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Events\Dispatcher;
use Viserio\Events\Tests\Fixture\EventListener;

class DispatcherTest extends TestCase
{
    public const coreRequest   = 'core.request';
    public const coreException = 'core.exception';
    public const apiRequest    = 'api.request';
    public const apiException  = 'api.exception';

    private $dispatcher;
    private $listener;

    public function setup()
    {
        $this->dispatcher = new Dispatcher(new ArrayContainer([]));
        $this->listener   = new EventListener();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The event name must only contain the characters A-Z, a-z, 0-9, _, and '.'.
     */
    public function testNoValidName()
    {
        $this->dispatcher->attach('foo-bar', 'test', 100);
    }

    public function testInitialState()
    {
        $ee = $this->dispatcher;

        self::assertFalse($ee->hasListeners(self::coreRequest));
        self::assertFalse($ee->hasListeners(self::coreException));
        self::assertFalse($ee->hasListeners(self::apiRequest));
        self::assertFalse($ee->hasListeners(self::apiException));
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
        $ee->getListeners('*.foo');

        self::assertEquals([$callback2, $callback1], $ee->getListeners('foo'));
    }

    public function testHandleEvent()
    {
        $argResult = null;

        $ee = $this->dispatcher;

        $ee->attach('foo', function ($arg) use (&$argResult) {
            $argResult = $arg;
        });

        self::assertTrue($ee->trigger('foo', ['bar']));
        self::assertEquals('bar', $argResult);
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

        self::assertFalse($ee->trigger('foo', ['bar']));
        self::assertEquals(1, $argResult);
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

        self::assertFalse($ee->trigger('foo', ['bar']));
        self::assertEquals(2, $argResult);
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

        self::assertEquals(['c', 'a', 'b', 'd'], $result);
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

        self::assertTrue($result);

        $result = false;

        self::assertFalse($ee->detach('foo', self::class));
        self::assertTrue($ee->detach('foo', $callBack));

        $ee->trigger('foo');

        self::assertFalse($result);
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

        self::assertTrue($result);

        $result = false;

        self::assertFalse($ee->detach('bar', $callBack));

        $ee->trigger('foo');

        self::assertTrue($result);
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

        self::assertTrue($result);

        $result = false;

        self::assertTrue($ee->detach('foo', $callBack));
        self::assertFalse($ee->detach('foo', $callBack));

        $ee->trigger('foo');

        self::assertFalse($result);
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

        self::assertTrue($result);

        $result = false;

        $ee->removeAllListeners('foo');
        $ee->trigger('foo');

        self::assertFalse($result);
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

        self::assertTrue($result);

        $result = false;

        $ee->removeAllListeners();
        $ee->trigger('foo');

        self::assertFalse($result);
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

        self::assertEquals(1, $result);
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

        self::assertFalse($ee->trigger('foo', ['bar']));

        self::assertEquals(2, $argResult);
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

        self::assertEquals(2, $argResult);
    }

    public function testAddingAndRemovingWildcardListeners()
    {
        $ee = $this->dispatcher;

        $ee->attach('#', [$this->listener, 'onAny']);
        $ee->attach('core.*', [$this->listener, 'onCore']);
        $ee->attach('core2.*', [$this->listener, 'onCore']);
        $ee->attach('*.exception', [$this->listener, 'onException']);
        $ee->attach(self::coreRequest, [$this->listener, 'onCoreRequest']);

        self::assertNumberListenersAdded(3, self::coreRequest);
        self::assertNumberListenersAdded(3, self::coreException);
        self::assertNumberListenersAdded(1, self::apiRequest);
        self::assertNumberListenersAdded(2, self::apiException);

        $ee->detach('#', [$this->listener, 'onAny']);

        self::assertNumberListenersAdded(2, self::coreRequest);
        self::assertNumberListenersAdded(2, self::coreException);
        self::assertNumberListenersAdded(0, self::apiRequest);
        self::assertNumberListenersAdded(1, self::apiException);

        $ee->detach('core.*', [$this->listener, 'onCore']);

        self::assertNumberListenersAdded(1, self::coreRequest);
        self::assertNumberListenersAdded(1, self::coreException);
        self::assertNumberListenersAdded(0, self::apiRequest);
        self::assertNumberListenersAdded(1, self::apiException);

        $ee->detach('*.exception', [$this->listener, 'onException']);

        self::assertNumberListenersAdded(1, self::coreRequest);
        self::assertNumberListenersAdded(0, self::coreException);
        self::assertNumberListenersAdded(0, self::apiRequest);
        self::assertNumberListenersAdded(0, self::apiException);

        $ee->detach(self::coreRequest, [$this->listener, 'onCoreRequest']);

        self::assertNumberListenersAdded(0, self::coreRequest);
        self::assertNumberListenersAdded(0, self::coreException);
        self::assertNumberListenersAdded(0, self::apiRequest);
        self::assertNumberListenersAdded(0, self::apiException);

        $ee->detach('empty.*', '');
    }

    public function testAddedListenersWithWildcardsAreRegisteredLazily()
    {
        $ee = $this->dispatcher;

        $ee->attach('#', [$this->listener, 'onAny']);

        self::assertTrue($ee->hasListeners(self::coreRequest));
        self::assertNumberListenersAdded(1, self::coreRequest);

        self::assertTrue($ee->hasListeners(self::coreException));
        self::assertNumberListenersAdded(1, self::coreException);

        self::assertTrue($ee->hasListeners(self::apiRequest));
        self::assertNumberListenersAdded(1, self::apiRequest);

        self::assertTrue($ee->hasListeners(self::apiException));
        self::assertNumberListenersAdded(1, self::apiException);
    }

    public function testAttachToUnsetSyncedEventsIfMatchRegex()
    {
        $ee = $this->dispatcher;

        $ee->attach('core.*', [$this->listener, 'onCore']);

        self::assertNumberListenersAdded(1, self::coreRequest);

        $ee->attach('core.*', [$this->listener, 'onCore']);

        self::assertNumberListenersAdded(2, self::coreRequest);
    }

    public function testTrigger()
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

        self::assertEquals(4, $this->listener->onAnyInvoked);
        self::assertEquals(2, $this->listener->onCoreInvoked);
        self::assertEquals(1, $this->listener->onCoreRequestInvoked);
        self::assertEquals(2, $this->listener->onExceptionInvoked);
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

        self::assertEquals(
            0,
            $listenerProviderInvoked,
            'The listener provider should not be invoked until the listener is requested'
        );

        $ee->trigger('foo');

        self::assertEquals([$listenerProvider], $ee->getListeners('foo'));
        self::assertEquals(
            1,
            $listenerProviderInvoked,
            'The listener provider should be invoked when the listener is requested'
        );

        self::assertEquals([$listenerProvider], $ee->getListeners('foo'));
        self::assertEquals(1, $listenerProviderInvoked, 'The listener provider should only be invoked once');
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

        return self::assertEquals($expected, count($ee->getListeners($eventName)));
    }
}
