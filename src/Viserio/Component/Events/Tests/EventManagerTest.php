<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Events\Event;
use Viserio\Component\Events\EventManager;
use Viserio\Component\Events\Tests\Fixture\EventListener;
use Viserio\Component\Events\Tests\Fixture\ExtendedEventManger;

/**
 * @internal
 */
final class EventManagerTest extends TestCase
{
    private const COREREQUEST   = 'core.request';
    private const COREEXCEPTION = 'core.exception';
    private const APIREQUEST    = 'api.request';
    private const APIEXCEPTION  = 'api.exception';

    /**
     * @var \Viserio\Component\Events\Tests\Fixture\ExtendedEventManger
     */
    private $dispatcher;

    /**
     * @var \Viserio\Component\Events\Tests\Fixture\EventListener
     */
    private $listener;

    /**
     * {@inheritdoc}
     */
    protected function setup(): void
    {
        $this->dispatcher = new ExtendedEventManger();
        $this->listener   = new EventListener();
    }

    public function testNoValidName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The event name must only contain the characters A-Z, a-z, 0-9, _, and \'.\'.');

        $this->dispatcher->attach('foo-bar', 'test', 100);
    }

    public function testInitialState(): void
    {
        $this->assertFalse($this->dispatcher->hasListeners(self::COREREQUEST));
        $this->assertFalse($this->dispatcher->hasListeners(self::COREEXCEPTION));
        $this->assertFalse($this->dispatcher->hasListeners(self::APIREQUEST));
        $this->assertFalse($this->dispatcher->hasListeners(self::APIEXCEPTION));
    }

    public function testListeners(): void
    {
        $callback1 = static function (): void {
        };
        $callback2 = static function (): void {
        };

        $this->dispatcher->attach('foo', $callback1, 100);
        $this->dispatcher->attach('foo', $callback2, 200);
        $this->dispatcher->getListeners('*.foo');

        $this->assertEquals([$callback2, $callback1], $this->dispatcher->getListeners('foo'));
    }

    public function testHandleEvent(): void
    {
        $event = null;

        $this->dispatcher->attach('foo', static function ($arg) use (&$event): void {
            $event = $arg;
        });

        $this->assertTrue($this->dispatcher->trigger('foo', ['bar']));
        $this->assertEquals(['bar'], $event->getTarget());
        $this->assertEquals('foo', $event->getName());
    }

    /**
     * @depends testHandleEvent
     */
    public function testCancelEvent(): void
    {
        $argResult = 0;
        $this->dispatcher->attach('foo', static function ($arg) use (&$argResult) {
            $argResult = 1;

            return false;
        });
        $this->dispatcher->attach('foo', static function ($arg) use (&$argResult): void {
            $argResult = 2;
        });

        $this->assertFalse($this->dispatcher->trigger('foo', ['bar']));
        $this->assertEquals(1, $argResult);
    }

    /**
     * @depends testHandleEvent
     */
    public function testCancelEventWithIsPropagationStopped(): void
    {
        $argResult = 0;
        $this->dispatcher->attach('foo', static function ($arg) use (&$argResult): void {
            $argResult = 1;
        });
        $this->dispatcher->attach('foo', static function ($arg) use (&$argResult): void {
            $argResult = 2;
        });

        $event = new Event('foo');
        $event->stopPropagation();

        $this->assertFalse($this->dispatcher->trigger($event, ['bar']));
        $this->assertEquals(0, $argResult);
    }

    /**
     * @depends testCancelEvent
     */
    public function testPriority(): void
    {
        $argResult = 0;
        $this->dispatcher->attach('foo', static function ($arg) use (&$argResult) {
            $argResult = 1;

            return false;
        });
        $this->dispatcher->attach('foo', static function ($arg) use (&$argResult) {
            $argResult = 2;

            return false;
        }, 1);

        $this->assertFalse($this->dispatcher->trigger('foo', ['bar']));
        $this->assertEquals(2, $argResult);
    }

    /**
     * @depends testPriority
     */
    public function testPriority2(): void
    {
        $result = [];
        $this->dispatcher->attach('foo', static function () use (&$result): void {
            $result[] = 'a';
        }, 200);
        $this->dispatcher->attach('foo', static function () use (&$result): void {
            $result[] = 'b';
        }, 50);
        $this->dispatcher->attach('foo', static function () use (&$result): void {
            $result[] = 'c';
        }, 300);
        $this->dispatcher->attach('foo', static function () use (&$result): void {
            $result[] = 'd';
        });
        $this->dispatcher->trigger('foo');

        $this->assertEquals(['c', 'a', 'b', 'd'], $result);
    }

    public function testDetach(): void
    {
        $result = false;

        $callBack = static function () use (&$result): void {
            $result = true;
        };
        $this->dispatcher->attach('foo', $callBack);
        $this->dispatcher->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertFalse($this->dispatcher->detach('foo', static function (): void {
        }));
        $this->assertTrue($this->dispatcher->detach('foo', $callBack));

        $this->dispatcher->trigger('foo');

        $this->assertFalse($result);
    }

    public function testRemoveUnknownListener(): void
    {
        $result = false;

        $callBack = static function () use (&$result): void {
            $result = true;
        };
        $this->dispatcher->attach('foo', $callBack);
        $this->dispatcher->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertFalse($this->dispatcher->detach('bar', $callBack));
        $this->assertTrue($this->dispatcher->trigger('foo'));
    }

    public function testRemoveListenerTwice(): void
    {
        $result = false;

        $callBack = static function () use (&$result): void {
            $result = true;
        };
        $this->dispatcher->attach('foo', $callBack);
        $this->dispatcher->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $this->assertTrue($this->dispatcher->detach('foo', $callBack));
        $this->assertFalse($this->dispatcher->detach('foo', $callBack));

        $this->dispatcher->trigger('foo');

        $this->assertFalse($result);
    }

    public function testClearListeners(): void
    {
        $result   = false;
        $callBack = static function () use (&$result): void {
            $result = true;
        };

        $this->dispatcher->attach('foo', $callBack);
        $this->dispatcher->trigger('foo');

        $this->assertTrue($result);

        $result = false;

        $this->dispatcher->clearListeners('foo');
        $this->dispatcher->trigger('foo');

        $this->assertFalse($result);
    }

    public function testRegisterSameListenerTwice(): void
    {
        $argResult = 0;

        $callback = static function () use (&$argResult): void {
            $argResult++;
        };

        $this->dispatcher->attach('foo', $callback);
        $this->dispatcher->attach('foo', $callback);
        $this->dispatcher->trigger('foo');

        $this->assertEquals(2, $argResult);
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

        $this->dispatcher->detach('empty.*', static function (): void {
        });
    }

    public function testAddedListenersWithWildcardsAreRegisteredLazily(): void
    {
        $this->dispatcher->attach('#', [$this->listener, 'onAny']);

        $this->assertTrue($this->dispatcher->hasListeners(self::COREREQUEST));
        $this->assertNumberListenersAdded(1, self::COREREQUEST);

        $this->assertTrue($this->dispatcher->hasListeners(self::COREEXCEPTION));
        $this->assertNumberListenersAdded(1, self::COREEXCEPTION);

        $this->assertTrue($this->dispatcher->hasListeners(self::APIREQUEST));
        $this->assertNumberListenersAdded(1, self::APIREQUEST);

        $this->assertTrue($this->dispatcher->hasListeners(self::APIEXCEPTION));
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

        $this->assertEquals(4, $this->listener->onAnyInvoked);
        $this->assertEquals(2, $this->listener->onCoreInvoked);
        $this->assertEquals(1, $this->listener->onCoreRequestInvoked);
        $this->assertEquals(2, $this->listener->onExceptionInvoked);
    }

    public function testLazyListenerInitializatiattach(): void
    {
        $listenerProviderInvoked = 0;

        $listenerProvider = static function () use (&$listenerProviderInvoked) {
            $listenerProviderInvoked++;

            return 'callback';
        };

        $this->dispatcher->attach('foo', $listenerProvider);

        $this->assertEquals(
            0,
            $listenerProviderInvoked,
            'The listener provider should not be invoked until the listener is requested'
        );

        $this->dispatcher->trigger('foo');

        $this->assertEquals([$listenerProvider], $this->dispatcher->getListeners('foo'));
        $this->assertEquals(
            1,
            $listenerProviderInvoked,
            'The listener provider should be invoked when the listener is requested'
        );

        $this->assertEquals([$listenerProvider], $this->dispatcher->getListeners('foo'));
        $this->assertEquals(1, $listenerProviderInvoked, 'The listener provider should only be invoked once');
    }

    public function testTriggerLazyListener(): void
    {
        $called  = 0;
        $factory = function () use (&$called) {
            $called++;

            return $this->listener;
        };
        $ee = new EventManager();

        $this->dispatcher->attach('foo', [$factory, 'onAny']);

        $this->assertSame(0, $called);

        $this->dispatcher->trigger('foo', $this->listener);
        $this->dispatcher->trigger('foo', $this->listener);

        $this->assertSame(1, $called);
    }

    public function testRemoveFindsLazyListeners(): void
    {
        $factory = function () {
            return $this->listener;
        };

        $this->dispatcher->attach('foo', [$factory, 'onAny']);

        $this->assertTrue($this->dispatcher->hasListeners('foo'));

        $this->dispatcher->detach('foo', [$this->listener, 'onAny']);

        $this->assertFalse($this->dispatcher->hasListeners('foo'));

        $this->dispatcher->attach('foo', [$this->listener, 'onAny']);

        $this->assertTrue($this->dispatcher->hasListeners('foo'));

        /** @var callable $callback */
        $callback = [$factory, 'onAny'];

        $this->dispatcher->detach('foo', $callback);

        $this->assertFalse($this->dispatcher->hasListeners('foo'));
    }

    public function testPriorityFindsLazyListeners(): void
    {
        $factory = function () {
            return $this->listener;
        };

        $this->dispatcher->attach('foo', [$factory, 'onAny'], 3);

        $this->assertSame(3, $this->dispatcher->getListenerPriority('foo', [$this->listener, 'onAny']));

        $this->dispatcher->detach('foo', [$factory, 'onAny']);
        $this->dispatcher->attach('foo', [$this->listener, 'onAny'], 5);

        $this->assertSame(5, $this->dispatcher->getListenerPriority('foo', [$factory, 'onAny']));
    }

    public function testGetLazyListeners(): void
    {
        $factory = function () {
            return $this->listener;
        };

        $this->dispatcher->attach('foo', [$factory, 'onAny'], 3);

        $this->assertSame([[$this->listener, 'onAny']], $this->dispatcher->getListeners('foo'));

        $this->dispatcher->detach('foo', [$this->listener, 'onAny']);
        $this->dispatcher->attach('bar', [$factory, 'onAny'], 3);

        $this->assertSame(['bar' => [[$this->listener, 'onAny']]], $this->dispatcher->getListeners());
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
        $this->assertCount($expected, $this->dispatcher->getListeners($eventName));
    }
}
