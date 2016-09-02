<?php
declare(strict_types=1);
namespace Viserio\Bus\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Bus\Dispatcher;
use Viserio\Contracts\Bus\Dispatcher as DispatcherContract;
use Viserio\Bus\Providers\BusServiceProvider;

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
