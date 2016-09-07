<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Routing\Providers\RoutingServiceProvider;
use Viserio\Routing\Router;

class RoutingServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());

        $this->assertInstanceOf(Router::class, $container->get(Router::class));
        $this->assertInstanceOf(Router::class, $container->get('router'));
    }
}
