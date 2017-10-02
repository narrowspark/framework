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

class TraceableEventManagerTest extends MockeryTestCase
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

        $this->wrapperDispatcher = new TraceableEventManager($this->dispatcher, new Stopwatch());
        $this->listener          = new EventListener();
    }

    public function testGetListenerPriority()
    {
        $this->wrapperDispatcher->attach('foo', function () {
        }, 123);

        $listeners = $this->dispatcher->getListeners('foo');

        self::assertSame(123, NSA::invokeMethod($this->wrapperDispatcher, 'getListenerPriority', 'foo', $listeners[0]));

        // Verify that priority is preserved when listener is removed and re-added
        // in preProcess() and postProcess().
        $this->wrapperDispatcher->trigger('foo');

        $listeners = $this->dispatcher->getListeners('foo');

        self::assertSame(123, NSA::invokeMethod($this->wrapperDispatcher, 'getListenerPriority', 'foo', $listeners[0]));
    }

    public function testGetListenerPriorityWhileDispatching()
    {
        $dispatcher               = $this->wrapperDispatcher;
        $priorityWhileDispatching = null;

        $listener = function () use ($dispatcher, &$priorityWhileDispatching, &$listener) {
            $priorityWhileDispatching = NSA::invokeMethod($dispatcher, 'getListenerPriority', 'bar', $listener);
        };

        $dispatcher->attach('bar', $listener, 5);

        self::assertTrue($dispatcher->trigger('bar'));
        self::assertSame(5, $priorityWhileDispatching);
    }

    /**
     * @internal test
     */
    public function testGetListeners()
    {
        $this->wrapperDispatcher->attach('foo', $listener = function () {
        });

        self::assertSame($this->dispatcher->getListeners('foo'), $this->wrapperDispatcher->getListeners('foo'));
    }

    public function testItReturnsNoOrphanedEventsWhenCreated()
    {
        $events = $this->wrapperDispatcher->getOrphanedEvents();

        self::assertEmpty($events);
    }

    public function testItReturnsOrphanedEventsAfterDispatch()
    {
        $this->wrapperDispatcher->trigger('foo');

        $events = $this->wrapperDispatcher->getOrphanedEvents();

        self::assertCount(1, $events);
        self::assertEquals(['foo'], $events);
    }

    public function testItDoesNotReturnHandledEvents()
    {
        $this->wrapperDispatcher->attach('foo', function () {
        });
        $this->wrapperDispatcher->trigger('foo');

        $events = $this->wrapperDispatcher->getOrphanedEvents();

        self::assertEmpty($events);
    }

    public function testLogger()
    {
        $logger = $this->mock(LoggerInterface::class);

        $this->wrapperDispatcher->setLogger($logger);
        $this->wrapperDispatcher->attach('foo', $listener1 = function () {
        });
        $this->wrapperDispatcher->attach('foo', $listener2 = function () {
        });

        $logger->shouldReceive('debug')
            ->with('Notified event "{event}" to listener "{listener}".', ['event' => 'foo', 'listener' => 'closure'])
            ->twice();

        $this->wrapperDispatcher->trigger('foo');
    }

    public function testLoggerWithStoppedEvent()
    {
        $logger = $this->mock(LoggerInterface::class);

        $this->wrapperDispatcher->setLogger($logger);
        $this->wrapperDispatcher->attach('foo', $listener1 = function (Event $event) {
            $event->stopPropagation();
        });
        $this->wrapperDispatcher->attach('foo', $listener2 = function () {
        });

        $logger->shouldReceive('debug')
            ->with('Notified event "{event}" to listener "{listener}".', ['event' => 'foo', 'listener' => 'closure']);

        $logger->shouldReceive('debug')
            ->with('Listener "{listener}" stopped propagation of the event "{event}".', ['event' => 'foo', 'listener' => 'closure']);

        $logger->shouldReceive('debug')
            ->with('Listener "{listener}" was not called for event "{event}".', ['event' => 'foo', 'listener' => 'closure']);

        $this->wrapperDispatcher->trigger('foo');
    }

    public function testAttachAndDetach()
    {
        $this->wrapperDispatcher->attach('foo', $listener = function () {
        });

        $listeners = $this->dispatcher->getListeners('foo');

        self::assertCount(1, $listeners);
        self::assertSame($listener, $listeners[0]);

        $this->wrapperDispatcher->detach('foo', $listener);

        self::assertCount(0, $this->dispatcher->getListeners('foo'));
    }

    public function testDispatchCallListeners()
    {
        $called = [];

        $this->wrapperDispatcher->attach('foo', function () use (&$called) {
            $called[] = 'foo1';
        }, 10);
        $this->wrapperDispatcher->attach('foo', function () use (&$called) {
            $called[] = 'foo2';
        }, 20);
        $this->wrapperDispatcher->trigger('foo');

        self::assertSame(['foo2', 'foo1'], $called);
    }

    public function testDispatchNested()
    {
        $dispatcher       = $this->wrapperDispatcher;
        $loop             = 1;
        $dispatchedEvents = 0;

        $dispatcher->attach('foo', $listener1 = function () use ($dispatcher, &$loop) {
            ++$loop;
            if (2 == $loop) {
                $dispatcher->trigger('foo');
            }
        });
        $dispatcher->attach('foo', function () use (&$dispatchedEvents) {
            ++$dispatchedEvents;
        });
        $dispatcher->trigger('foo');

        self::assertSame(2, $dispatchedEvents);
    }

    public function testDispatchReusedEventNested()
    {
        $nestedCall = false;
        $dispatcher = $this->wrapperDispatcher;

        $dispatcher->attach('foo', function (Event $e) use ($dispatcher) {
            $dispatcher->trigger('bar', $e);
        });
        $dispatcher->attach('bar', function (Event $e) use (&$nestedCall) {
            $nestedCall = true;
        });

        self::assertFalse($nestedCall);

        $dispatcher->trigger('foo');

        self::assertTrue($nestedCall);
    }

    public function testListenerCanRemoveItselfWhenExecuted()
    {
        $eventDispatcher = $this->wrapperDispatcher;

        $listener1 = function ($event) use (&$listener1, $eventDispatcher) {
            $eventDispatcher->detach('foo', $listener1);
        };

        $eventDispatcher->attach('foo', $listener1);
        $eventDispatcher->attach('foo', function () {
        });
        $eventDispatcher->trigger('foo');

        self::assertCount(1, $eventDispatcher->getListeners('foo'), 'expected listener1 to be removed');
    }
}
