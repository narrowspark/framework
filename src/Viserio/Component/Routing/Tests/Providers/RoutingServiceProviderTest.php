<?php
declare(strict_types=1);
namespace Viserio\Component\Routing\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Events\Providers\EventsServiceProvider;
use Viserio\Component\Routing\Providers\RoutingServiceProvider;
use Viserio\Component\Routing\Router;

class RoutingServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());
        $container->register(new EventsServiceProvider());

        self::assertInstanceOf(Router::class, $container->get(Router::class));
        self::assertInstanceOf(Router::class, $container->get('router'));
    }
}
