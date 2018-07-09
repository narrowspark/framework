<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Events\EventManager as EventManagerContract;
use Viserio\Component\Events\EventManager;
use Viserio\Component\Events\Provider\EventsServiceProvider;

/**
 * @internal
 */
final class EventsServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());

        static::assertInstanceOf(EventManagerContract::class, $container->get(EventManagerContract::class));
        static::assertInstanceOf(EventManagerContract::class, $container->get(EventManager::class));
        static::assertInstanceOf(EventManagerContract::class, $container->get('events'));
    }
}
