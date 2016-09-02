<?php
declare(strict_types=1);
namespace Viserio\Bus\Tests\Providers;

use Viserio\Bus\Dispatcher;
use Viserio\Bus\Providers\BusServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Bus\Dispatcher as DispatcherContract;

class BusServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new BusServiceProvider());

        $this->assertInstanceOf(Dispatcher::class, $container->get(Dispatcher::class));
        $this->assertInstanceOf(DispatcherContract::class, $container->get(DispatcherContract::class));
    }
}
