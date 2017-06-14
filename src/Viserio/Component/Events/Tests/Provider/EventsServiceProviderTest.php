<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Events\EventManager;
use Viserio\Component\Events\Provider\EventsServiceProvider;

class EventsServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());

        self::assertInstanceOf(EventManagerContract::class, $container->get(EventManagerContract::class));
        self::assertInstanceOf(EventManagerContract::class, $container->get(EventManager::class));
        self::assertInstanceOf(EventManagerContract::class, $container->get('events'));
    }
}
