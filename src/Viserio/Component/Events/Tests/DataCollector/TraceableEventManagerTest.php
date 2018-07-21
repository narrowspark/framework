<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Nyholm\NSA;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Events\DataCollector\TraceableEventManager;
use Viserio\Component\Events\Event;
use Viserio\Component\Events\EventManager;
use Viserio\Component\Events\Tests\Fixture\EventListener;

/**
 * @internal
 */
final class TraceableEventManagerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Events\EventManager
     */
    private $dispatcher;

    /**
     * @var \Viserio\Component\Events\DataCollector\TraceableEventManager
     */
    private $wrapperDispatcher;

    /**
     * @var \Viserio\Component\Events\Tests\Fixture\EventListener
     */
    private $listener;

    /**
     * {@inheritdoc}
     */
    protected function setup(): void
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

        $this->wrapperDispatcher = new TraceableEventManager($this->dispatcher, new Stopwatch());
        $this->listener          = new EventListener();
    }

    public function testGetListenerPriority(): void
    {
        $this->wrapperDispatcher->attach('foo', function (): void {
        }, 123);

        $listeners = $this->dispatcher->getListeners('foo');

        static::assertSame(123, NSA::invokeMethod($this->wrapperDispatcher, 'getListenerPriority', 'foo', $listeners[0]));

        // Verify that priority is preserved when listener is removed and re-added
        // in preProcess() and postProcess().
        $this->wrapperDispatcher->trigger('foo');

        $listeners = $this->dispatcher->getListeners('foo');

        static::assertSame(123, NSA::invokeMethod($this->wrapperDispatcher, 'getListenerPriority', 'foo', $listeners[0]));
    }

    public function testGetListenerPriorityWhileDispatching(): void
    {
        $dispatcher               = $this->wrapperDispatcher;
        $priorityWhileDispatching = null;

        $listener = function () use ($dispatcher, &$priorityWhileDispatching, &$listener): void {
            $priorityWhileDispatching = NSA::invokeMethod($dispatcher, 'getListenerPriority', 'bar', $listener);
        };

        $dispatcher->attach('bar', $listener, 5);

        static::assertTrue($dispatcher->trigger('bar'));
        static::assertSame(5, $priorityWhileDispatching);
    }

    /**
     * @internal test
     */
    public function testGetListeners(): void
    {
        $this->wrapperDispatcher->attach('foo', $listener = function (): void {
        });

        static::assertSame($this->dispatcher->getListeners('foo'), $this->wrapperDispatcher->getListeners('foo'));
    }

    public function testItReturnsNoOrphanedEventsWhenCreated(): void
    {
        $events = $this->wrapperDispatcher->getOrphanedEvents();

        static::assertEmpty($events);
    }

    public function testItReturnsOrphanedEventsAfterDispatch(): void
    {
        $this->wrapperDispatcher->trigger('foo');

        $events = $this->wrapperDispatcher->getOrphanedEvents();

        static::assertCount(1, $events);
        static::assertEquals(['foo'], $events);
    }

    public function testItDoesNotReturnHandledEvents(): void
    {
        $this->wrapperDispatcher->attach('foo', function (): void {
        });
        $this->wrapperDispatcher->trigger('foo');

        $events = $this->wrapperDispatcher->getOrphanedEvents();

        static::assertEmpty($events);
    }

    public function testLogger(): void
    {
        $logger = $this->mock(LoggerInterface::class);

        $this->wrapperDispatcher->setLogger($logger);
        $this->wrapperDispatcher->attach('foo', $listener1 = function (): void {
        });
        $this->wrapperDispatcher->attach('foo', $listener2 = function (): void {
        });

        $logger->shouldReceive('debug')
            ->with('Notified event "{event}" to listener "{listener}".', ['event' => 'foo', 'listener' => 'closure'])
            ->twice();

        $this->wrapperDispatcher->trigger('foo');
    }

    public function testLoggerWithStoppedEvent(): void
    {
        $logger = $this->mock(LoggerInterface::class);

        $this->wrapperDispatcher->setLogger($logger);
        $this->wrapperDispatcher->attach('foo', $listener1 = function (Event $event): void {
            $event->stopPropagation();
        });
        $this->wrapperDispatcher->attach('foo', $listener2 = function (): void {
        });

        $logger->shouldReceive('debug')
            ->with('Notified event "{event}" to listener "{listener}".', ['event' => 'foo', 'listener' => 'closure']);

        $logger->shouldReceive('debug')
            ->with('Listener "{listener}" stopped propagation of the event "{event}".', ['event' => 'foo', 'listener' => 'closure']);

        $logger->shouldReceive('debug')
            ->with('Listener "{listener}" was not called for event "{event}".', ['event' => 'foo', 'listener' => 'closure']);

        $this->wrapperDispatcher->trigger('foo');
    }

    public function testAttachAndDetach(): void
    {
        $this->wrapperDispatcher->attach('foo', $listener = function (): void {
        });

        $listeners = $this->dispatcher->getListeners('foo');

        static::assertCount(1, $listeners);
        static::assertSame($listener, $listeners[0]);

        $this->wrapperDispatcher->detach('foo', $listener);

        static::assertCount(0, $this->dispatcher->getListeners('foo'));
    }

    public function testDispatchCallListeners(): void
    {
        $called = [];

        $this->wrapperDispatcher->attach('foo', function () use (&$called): void {
            $called[] = 'foo1';
        }, 10);
        $this->wrapperDispatcher->attach('foo', function () use (&$called): void {
            $called[] = 'foo2';
        }, 20);
        $this->wrapperDispatcher->trigger('foo');

        static::assertSame(['foo2', 'foo1'], $called);
    }

    public function testDispatchNested(): void
    {
        $dispatcher       = $this->wrapperDispatcher;
        $loop             = 1;
        $dispatchedEvents = 0;

        $dispatcher->attach('foo', $listener1 = function () use ($dispatcher, &$loop): void {
            $loop++;

            if (2 === $loop) {
                $dispatcher->trigger('foo');
            }
        });
        $dispatcher->attach('foo', function () use (&$dispatchedEvents): void {
            $dispatchedEvents++;
        });
        $dispatcher->trigger('foo');

        static::assertSame(2, $dispatchedEvents);
    }

    public function testDispatchReusedEventNested(): void
    {
        $nestedCall = false;
        $dispatcher = $this->wrapperDispatcher;

        $dispatcher->attach('foo', function (Event $e) use ($dispatcher): void {
            $dispatcher->trigger('bar', $e);
        });
        $dispatcher->attach('bar', function (Event $e) use (&$nestedCall): void {
            $nestedCall = true;
        });

        static::assertFalse($nestedCall);

        $dispatcher->trigger('foo');

        static::assertTrue($nestedCall);
    }

    public function testListenerCanRemoveItselfWhenExecuted(): void
    {
        $eventDispatcher = $this->wrapperDispatcher;

        $listener1 = function ($event) use (&$listener1, $eventDispatcher): void {
            $eventDispatcher->detach('foo', $listener1);
        };

        $eventDispatcher->attach('foo', $listener1);
        $eventDispatcher->attach('foo', function (): void {
        });
        $eventDispatcher->trigger('foo');

        static::assertCount(1, $eventDispatcher->getListeners('foo'), 'expected listener1 to be removed');
    }

    public function testClearCalledListeners(): void
    {
        $this->wrapperDispatcher->attach('foo', function (): void {
        }, 5);

        $this->wrapperDispatcher->trigger('foo');
        $this->wrapperDispatcher->reset();

        $listeners = $this->wrapperDispatcher->getNotCalledListeners();

        static::assertArrayHasKey('stub', $listeners['foo'][0]);

        static::assertEquals([], $this->wrapperDispatcher->getCalledListeners());
    }

    public function testClearOrphanedEvents()
    {
        $eventDispatcher = $this->wrapperDispatcher;

        $eventDispatcher->trigger('foo');

        $events = $eventDispatcher->getOrphanedEvents();

        static::assertCount(1, $events);

        $eventDispatcher->reset();

        $events = $eventDispatcher->getOrphanedEvents();

        static::assertCount(0, $events);
    }
}
