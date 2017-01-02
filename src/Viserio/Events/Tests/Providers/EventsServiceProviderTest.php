<?php
declare(strict_types=1);
namespace Viserio\Events\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Events\Dispatcher;
use Viserio\Events\Providers\EventsServiceProvider;
use PHPUnit\Framework\TestCase;

class EventsServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new EventsServiceProvider());

        self::assertInstanceOf(Dispatcher::class, $container->get(Dispatcher::class));
    }
}
