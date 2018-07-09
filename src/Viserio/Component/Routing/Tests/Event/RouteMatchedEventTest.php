<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Events;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Routing\Dispatcher as DispatcherContract;
use Viserio\Component\Contract\Routing\Route as RouteContract;
use Viserio\Component\Routing\Event\RouteMatchedEvent;

/**
 * @internal
 */
final class RouteMatchedEventTest extends MockeryTestCase
{
    public function testGetServerRequest(): void
    {
        $event = new RouteMatchedEvent(
            $this->mock(DispatcherContract::class),
            $this->mock(RouteContract::class),
            $this->mock(ServerRequestInterface::class)
        );

        static::assertInstanceOf(ServerRequestInterface::class, $event->getServerRequest());
    }

    public function testGetRoute(): void
    {
        $event = new RouteMatchedEvent(
            $this->mock(DispatcherContract::class),
            $this->mock(RouteContract::class),
            $this->mock(ServerRequestInterface::class)
        );

        static::assertInstanceOf(RouteContract::class, $event->getRoute());
    }
}
