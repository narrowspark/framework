<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
 *
 * @small
 */
final class TraceableEventManagerTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Events\EventManager */
    private $dispatcher;

    /** @var \Viserio\Component\Events\DataCollector\TraceableEventManager */
    private $wrapperDispatcher;

    /** @var \Viserio\Component\Events\Tests\Fixture\EventListener */
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
        $this->listener = new EventListener();
    }

    public function testGetListenerPriority(): void
    {
        $this->wrapperDispatcher->attach('foo', static function (): void {
        }, 123);

        $listeners = $this->dispatcher->getListeners('foo');

        self::assertSame(123, NSA::invokeMethod($this->wrapperDispatcher, 'getListenerPriority', 'foo', $listeners[0]));

        // Verify that priority is preserved when listener is removed and re-added
        // in preProcess() and postProcess().
        $this->wrapperDispatcher->trigger('foo');

        $listeners = $this->dispatcher->getListeners('foo');

        self::assertSame(123, NSA::invokeMethod($this->wrapperDispatcher, 'getListenerPriority', 'foo', $listeners[0]));
    }

    public function testGetListenerPriorityWhileDispatching(): void
    {
        $dispatcher = $this->wrapperDispatcher;
        $priorityWhileDispatching = null;

        $listener = static function () use ($dispatcher, &$priorityWhileDispatching, &$listener): void {
            $priorityWhileDispatching = NSA::invokeMethod($dispatcher, 'getListenerPriority', 'bar', $listener);
        };

        $dispatcher->attach('bar', $listener, 5);

        self::assertTrue($dispatcher->trigger('bar'));
        self::assertSame(5, $priorityWhileDispatching);
    }

    /**
     * @internal test
     */
    public function testGetListeners(): void
    {
        $this->wrapperDispatcher->attach('foo', $listener = static function (): void {
        });

        self::assertSame($this->dispatcher->getListeners('foo'), $this->wrapperDispatcher->getListeners('foo'));
    }

    public function testItReturnsNoOrphanedEventsWhenCreated(): void
    {
        $events = $this->wrapperDispatcher->getOrphanedEvents();

        self::assertEmpty($events);
    }

    public function testItReturnsOrphanedEventsAfterDispatch(): void
    {
        $this->wrapperDispatcher->trigger('foo');

        $events = $this->wrapperDispatcher->getOrphanedEvents();

        self::assertCount(1, $events);
        self::assertEquals(['foo'], $events);
    }

    public function testItDoesNotReturnHandledEvents(): void
    {
        $this->wrapperDispatcher->attach('foo', static function (): void {
        });
        $this->wrapperDispatcher->trigger('foo');

        $events = $this->wrapperDispatcher->getOrphanedEvents();

        self::assertEmpty($events);
    }

    public function testLogger(): void
    {
        $logger = \Mockery::mock(LoggerInterface::class);

        $this->wrapperDispatcher->setLogger($logger);
        $this->wrapperDispatcher->attach('foo', $listener1 = static function (): void {
        });
        $this->wrapperDispatcher->attach('foo', $listener2 = static function (): void {
        });

        $logger->shouldReceive('debug')
            ->with('Notified event "{event}" to listener "{listener}".', ['event' => 'foo', 'listener' => 'closure'])
            ->twice();

        $this->wrapperDispatcher->trigger('foo');
    }

    public function testLoggerWithStoppedEvent(): void
    {
        $logger = \Mockery::mock(LoggerInterface::class);

        $this->wrapperDispatcher->setLogger($logger);
        $this->wrapperDispatcher->attach('foo', $listener1 = static function (Event $event): void {
            $event->stopPropagation();
        });
        $this->wrapperDispatcher->attach('foo', $listener2 = static function (): void {
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
        $this->wrapperDispatcher->attach('foo', $listener = static function (): void {
        });

        $listeners = $this->dispatcher->getListeners('foo');

        self::assertCount(1, $listeners);
        self::assertSame($listener, $listeners[0]);

        $this->wrapperDispatcher->detach('foo', $listener);

        self::assertCount(0, $this->dispatcher->getListeners('foo'));
    }

    public function testDispatchCallListeners(): void
    {
        $called = [];

        $this->wrapperDispatcher->attach('foo', static function () use (&$called): void {
            $called[] = 'foo1';
        }, 10);
        $this->wrapperDispatcher->attach('foo', static function () use (&$called): void {
            $called[] = 'foo2';
        }, 20);
        $this->wrapperDispatcher->trigger('foo');

        self::assertSame(['foo2', 'foo1'], $called);
    }

    public function testDispatchNested(): void
    {
        $dispatcher = $this->wrapperDispatcher;
        $loop = 1;
        $dispatchedEvents = 0;

        $dispatcher->attach('foo', $listener1 = static function () use ($dispatcher, &$loop): void {
            $loop++;

            if (2 === $loop) {
                $dispatcher->trigger('foo');
            }
        });
        $dispatcher->attach('foo', static function () use (&$dispatchedEvents): void {
            $dispatchedEvents++;
        });
        $dispatcher->trigger('foo');

        self::assertSame(2, $dispatchedEvents);
    }

    public function testDispatchReusedEventNested(): void
    {
        $nestedCall = false;
        $dispatcher = $this->wrapperDispatcher;

        $dispatcher->attach('foo', static function (Event $e) use ($dispatcher): void {
            $dispatcher->trigger('bar', $e);
        });
        $dispatcher->attach('bar', static function (Event $e) use (&$nestedCall): void {
            $nestedCall = true;
        });

        self::assertFalse($nestedCall);

        $dispatcher->trigger('foo');

        self::assertTrue($nestedCall);
    }

    public function testListenerCanRemoveItselfWhenExecuted(): void
    {
        $eventDispatcher = $this->wrapperDispatcher;

        $listener1 = static function ($event) use (&$listener1, $eventDispatcher): void {
            $eventDispatcher->detach('foo', $listener1);
        };

        $eventDispatcher->attach('foo', $listener1);
        $eventDispatcher->attach('foo', static function (): void {
        });
        $eventDispatcher->trigger('foo');

        self::assertCount(1, $eventDispatcher->getListeners('foo'), 'expected listener1 to be removed');
    }

    public function testClearCalledListeners(): void
    {
        $this->wrapperDispatcher->attach('foo', static function (): void {
        }, 5);

        $this->wrapperDispatcher->trigger('foo');
        $this->wrapperDispatcher->reset();

        $listeners = $this->wrapperDispatcher->getNotCalledListeners();

        self::assertArrayHasKey('stub', $listeners['foo'][0]);

        self::assertEquals([], $this->wrapperDispatcher->getCalledListeners());
    }

    public function testClearOrphanedEvents(): void
    {
        $eventDispatcher = $this->wrapperDispatcher;

        $eventDispatcher->trigger('foo');

        $events = $eventDispatcher->getOrphanedEvents();

        self::assertCount(1, $events);

        $eventDispatcher->reset();

        $events = $eventDispatcher->getOrphanedEvents();

        self::assertCount(0, $events);
    }
}
