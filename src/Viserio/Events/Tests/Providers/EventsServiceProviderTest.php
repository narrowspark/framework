<?php
declare(strict_types=1);
namespace Viserio\Events\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Container\Container;
use Viserio\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Events\EventManager;
use Viserio\Events\Providers\EventsServiceProvider;

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
