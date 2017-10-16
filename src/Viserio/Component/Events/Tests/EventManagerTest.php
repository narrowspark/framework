<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Events\Event;
use Viserio\Component\Events\EventManager;
use Viserio\Component\Events\Tests\Fixture\EventListener;

class EventManagerTest extends TestCase
{
    private const COREREQUEST   = 'core.request';
    private const COREEXCEPTION = 'core.exception';
    private const APIREQUEST    = 'api.request';
    private const APIEXCEPTION  = 'api.exception';

    /**
     * @var \Viserio\Component\Events\EventManager
     */
    private $dispatcher;

    /**
     * @var \Viserio\Component\Events\Tests\Fixture\EventListener
     */
    private $listener;

    /**
     * {@inheritdoc}
     */
    public function setup(): void
    {
        $this->dispatcher = new class() extends EventManager {
            /**
             * Determine if a given event has listeners.
             *
             * @param string $eventName
             *
             * @return bool
             */
            public function hasListeners(string $eventName): bool
            {
                return \count($this->getListeners($eventName)) !== 0;
            }
        };
        $this->listener = new EventListener();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The event name must only contain the characters A-Z, a-z, 0-9, _, and '.'.
     */
    public function testNoValidName(): void
    {
        $this->dispatcher->attach('foo-bar', 'test', 100);
    }

    public function testInitialState(): void
    {
        $ee = $this->dispatcher;

        self::assertFalse($ee->hasListeners(self::COREREQUEST));
        self::assertFalse($ee->hasListeners(self::COREEXCEPTION));
        self::assertFalse($ee->hasListeners(self::APIREQUEST));
        self::assertFalse($ee->hasListeners(self::APIEXCEPTION));
    }

    public function testListeners(): void
    {
        $ee = $this->dispatcher;

        $callback1 = function (): void {
        };
        $callback2 = function (): void {
        };

        $ee->attach('foo', $callback1, 100);
        $ee->attach('foo', $callback2, 200);
        $ee->getListeners('*.foo');

        self::assertEquals([$callback2, $callback1], $ee->getListeners('foo'));
    }

    public function testHandleEvent(): void
    {
        $event = null;

        $ee = $this->dispatcher;

        $ee->attach('foo', function ($arg) use (&$event): void {
            $event = $arg;
        });

        self::assertTrue($ee->trigger('foo', ['bar']));
        self::assertEquals(['bar'], $event->getTarget());
        self::assertEquals('foo', $event->getName());
    }

    /**
     * @depends testHandleEvent
     */
    public function testCancelEvent(): void
    {
        $argResult = 0;

        $ee = $this->dispatcher;
        $ee->attach('foo', function ($arg) use (&$argResult) {
            $argResult = 1;

            return false;
        });
        $ee->attach('foo', function ($arg) use (&$argResult): void {
            $argResult = 2;
        });

        self::assertFalse($ee->trigger('foo', ['bar']));
        self::assertEquals(1, $argResult);
    }

    /**
     * @depends testHandleEvent
     */
    public function testCancelEventWithIsPropagationStopped(): void
    {
        $argResult = 0;

        $ee = $this->dispatcher;
        $ee->attach('foo', function ($arg) use (&$argResult): void {
            $argResult = 1;
        });
        $ee->attach('foo', function ($arg) use (&$argResult): void {
            $argResult = 2;
        });

        $event = new Event('foo');
        $event->stopPropagation();

        self::assertFalse($ee->trigger($event, ['bar']));
        self::assertEquals(0, $argResult);
    }

    /**
     * @depends testCancelEvent
     */
    public function testPriority(): void
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
    public function testPriority2(): void
    {
        $result = [];

        $ee = $this->dispatcher;
        $ee->attach('foo', function () use (&$result): void {
            $result[] = 'a';
        }, 200);
        $ee->attach('foo', function () use (&$result): void {
            $result[] = 'b';
        }, 50);
        $ee->attach('foo', function () use (&$result): void {
            $result[] = 'c';
        }, 300);
        $ee->attach('foo', function () use (&$result): void {
            $result[] = 'd';
        });
        $ee->trigger('foo');

        self::assertEquals(['c', 'a', 'b', 'd'], $result);
    }

    public function testoff(): void
    {
        $result = false;

        $callBack = function () use (&$result): void {
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

    public function testRemoveUnknownListener(): void
    {
        $result = false;

        $callBack = function () use (&$result): void {
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

    public function testRemoveListenerTwice(): void
    {
        $result = false;

        $callBack = function () use (&$result): void {
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

    public function testClearListeners(): void
    {
        $result = false;

        $callBack = function () use (&$result): void {
            $result = true;
        };

        $ee = $this->dispatcher;
        $ee->attach('foo', $callBack);
        $ee->trigger('foo');

        self::assertTrue($result);

        $result = false;

        $ee->clearListeners('foo');
        $ee->trigger('foo');

        self::assertFalse($result);
    }

    public function testRegisterSameListenerTwice(): void
    {
        $argResult = 0;

        $callback = function () use (&$argResult): void {
            $argResult++;
        };

        $ee = $this->dispatcher;

        $ee->attach('foo', $callback);
        $ee->attach('foo', $callback);
        $ee->trigger('foo');

        self::assertEquals(2, $argResult);
    }

    public function testAddingAndRemovingWildcardListeners(): void
    {
        $this->dispatcher->attach('#', [$this->listener, 'onAny']);
        $this->dispatcher->attach('core.*', [$this->listener, 'onCore']);
        $this->dispatcher->attach('core2.*', [$this->listener, 'onCore']);
        $this->dispatcher->attach('*.exception', [$this->listener, 'onException']);
        $this->dispatcher->attach(self::COREREQUEST, [$this->listener, 'onCoreRequest']);

        $this->assertNumberListenersAdded(3, self::COREREQUEST);
        $this->assertNumberListenersAdded(3, self::COREEXCEPTION);
        $this->assertNumberListenersAdded(1, self::APIREQUEST);
        $this->assertNumberListenersAdded(2, self::APIEXCEPTION);

        $this->dispatcher->detach('#', [$this->listener, 'onAny']);

        $this->assertNumberListenersAdded(2, self::COREREQUEST);
        $this->assertNumberListenersAdded(2, self::COREEXCEPTION);
        $this->assertNumberListenersAdded(0, self::APIREQUEST);
        $this->assertNumberListenersAdded(1, self::APIEXCEPTION);

        $this->dispatcher->detach('core.*', [$this->listener, 'onCore']);

        $this->assertNumberListenersAdded(1, self::COREREQUEST);
        $this->assertNumberListenersAdded(1, self::COREEXCEPTION);
        $this->assertNumberListenersAdded(0, self::APIREQUEST);
        $this->assertNumberListenersAdded(1, self::APIEXCEPTION);

        $this->dispatcher->detach('*.exception', [$this->listener, 'onException']);

        $this->assertNumberListenersAdded(1, self::COREREQUEST);
        $this->assertNumberListenersAdded(0, self::COREEXCEPTION);
        $this->assertNumberListenersAdded(0, self::APIREQUEST);
        $this->assertNumberListenersAdded(0, self::APIEXCEPTION);

        $this->dispatcher->detach(self::COREREQUEST, [$this->listener, 'onCoreRequest']);

        $this->assertNumberListenersAdded(0, self::COREREQUEST);
        $this->assertNumberListenersAdded(0, self::COREEXCEPTION);
        $this->assertNumberListenersAdded(0, self::APIREQUEST);
        $this->assertNumberListenersAdded(0, self::APIEXCEPTION);

        $this->dispatcher->detach('empty.*', '');
    }

    public function testAddedListenersWithWildcardsAreRegisteredLazily(): void
    {
        $this->dispatcher->attach('#', [$this->listener, 'onAny']);

        self::assertTrue($this->dispatcher->hasListeners(self::COREREQUEST));
        $this->assertNumberListenersAdded(1, self::COREREQUEST);

        self::assertTrue($this->dispatcher->hasListeners(self::COREEXCEPTION));
        $this->assertNumberListenersAdded(1, self::COREEXCEPTION);

        self::assertTrue($this->dispatcher->hasListeners(self::APIREQUEST));
        $this->assertNumberListenersAdded(1, self::APIREQUEST);

        self::assertTrue($this->dispatcher->hasListeners(self::APIEXCEPTION));
        $this->assertNumberListenersAdded(1, self::APIEXCEPTION);
    }

    public function testAttachToUnsetSyncedEventsIfMatchRegex(): void
    {
        $this->dispatcher->attach('core.*', [$this->listener, 'onCore']);

        $this->assertNumberListenersAdded(1, self::COREREQUEST);

        $this->dispatcher->attach('core.*', [$this->listener, 'onCore']);

        $this->assertNumberListenersAdded(2, self::COREREQUEST);
    }

    public function testTrigger(): void
    {
        $this->dispatcher->attach('#', [$this->listener, 'onAny']);
        $this->dispatcher->attach('core.*', [$this->listener, 'onCore']);
        $this->dispatcher->attach('*.exception', [$this->listener, 'onException']);
        $this->dispatcher->attach(self::COREREQUEST, [$this->listener, 'onCoreRequest']);

        $this->dispatcher->trigger(new Event(self::COREREQUEST));
        $this->dispatcher->trigger(self::COREEXCEPTION);
        $this->dispatcher->trigger(self::APIREQUEST);
        $this->dispatcher->trigger(self::APIEXCEPTION);

        self::assertEquals(4, $this->listener->onAnyInvoked);
        self::assertEquals(2, $this->listener->onCoreInvoked);
        self::assertEquals(1, $this->listener->onCoreRequestInvoked);
        self::assertEquals(2, $this->listener->onExceptionInvoked);
    }

    public function testLazyListenerInitializatiattach(): void
    {
        $listenerProviderInvoked = 0;

        $listenerProvider = function () use (&$listenerProviderInvoked) {
            $listenerProviderInvoked++;

            return 'callback';
        };

        $this->dispatcher->attach('foo', $listenerProvider);

        self::assertEquals(
            0,
            $listenerProviderInvoked,
            'The listener provider should not be invoked until the listener is requested'
        );

        $this->dispatcher->trigger('foo');

        self::assertEquals([$listenerProvider], $this->dispatcher->getListeners('foo'));
        self::assertEquals(
            1,
            $listenerProviderInvoked,
            'The listener provider should be invoked when the listener is requested'
        );

        self::assertEquals([$listenerProvider], $this->dispatcher->getListeners('foo'));
        self::assertEquals(1, $listenerProviderInvoked, 'The listener provider should only be invoked once');
    }

    public function testTriggerLazyListener(): void
    {
        $called  = 0;
        $factory = function () use (&$called) {
            $called++;

            return $this->listener;
        };
        $ee = new EventManager();

        $ee->attach('foo', [$factory, 'onAny']);

        self::assertSame(0, $called);

        $ee->trigger('foo', $this->listener);
        $ee->trigger('foo', $this->listener);

        self::assertSame(1, $called);
    }

    public function testRemoveFindsLazyListeners(): void
    {
        $factory = function () {
            return $this->listener;
        };

        $this->dispatcher->attach('foo', [$factory, 'onAny']);

        self::assertTrue($this->dispatcher->hasListeners('foo'));

        $this->dispatcher->detach('foo', [$this->listener, 'onAny']);

        self::assertFalse($this->dispatcher->hasListeners('foo'));

        $this->dispatcher->attach('foo', [$this->listener, 'onAny']);

        self::assertTrue($this->dispatcher->hasListeners('foo'));

        $this->dispatcher->detach('foo', [$factory, 'onAny']);

        self::assertFalse($this->dispatcher->hasListeners('foo'));
    }

    public function testPriorityFindsLazyListeners(): void
    {
        $factory = function () {
            return $this->listener;
        };

        $this->dispatcher->attach('foo', [$factory, 'onAny'], 3);
        self::assertSame(3, $this->dispatcher->getListenerPriority('foo', [$this->listener, 'onAny']));
        $this->dispatcher->detach('foo', [$factory, 'onAny']);

        $this->dispatcher->attach('foo', [$this->listener, 'onAny'], 5);
        self::assertSame(5, $this->dispatcher->getListenerPriority('foo', [$factory, 'onAny']));
    }

    public function testGetLazyListeners(): void
    {
        $factory = function () {
            return $this->listener;
        };

        $this->dispatcher->attach('foo', [$factory, 'onAny'], 3);

        self::assertSame([[$this->listener, 'onAny']], $this->dispatcher->getListeners('foo'));

        $this->dispatcher->detach('foo', [$this->listener, 'onAny']);
        $this->dispatcher->attach('bar', [$factory, 'onAny'], 3);

        self::assertSame(['bar' => [[$this->listener, 'onAny']]], $this->dispatcher->getListeners());
    }

    /**
     * Asserts the number of listeners added for a specific event or all events
     * in total.
     *
     * @param int    $expected
     * @param string $eventName
     *
     * @return void
     */
    private function assertNumberListenersAdded(int $expected, string $eventName): void
    {
        self::assertCount($expected, $this->dispatcher->getListeners($eventName));
    }
}
