<?php
declare(strict_types=1);
namespace Viserio\Routing\Tests\Providers;

use Viserio\Config\Providers\ConfigServiceProvider;
use Viserio\Container\Container;
use Viserio\Routing\Providers\RoutingServiceProvider;
use Viserio\Routing\Router;

class RoutingServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new RoutingServiceProvider());

        $container->get('config')->set('routing', [
            'path' => '../Cache/',
        ]);

        $this->assertInstanceOf(Router::class, $container->get(Router::class));
        $this->assertInstanceOf(Router::class, $container->get('router'));
    }

    public function testProviderWithoutConfigManager()
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());

        $container->instance('options', [
            'path' => '../Cache/',
        ]);

        $this->assertInstanceOf(Router::class, $container->get(Router::class));
        $this->assertInstanceOf(Router::class, $container->get('router'));
    }

    public function testProviderWithoutConfigManagerAndNamespace()
    {
        $container = new Container();
        $container->register(new RoutingServiceProvider());

        $container->instance('viserio.routing.options', [
            'path' => '../Cache/',
        ]);

        $this->assertInstanceOf(Router::class, $container->get(Router::class));
        $this->assertInstanceOf(Router::class, $container->get('router'));
    }
}
