<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Bus\Providers\BusServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Bus\Dispatcher as DispatcherContract;

class BusServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new BusServiceProvider());

        self::assertInstanceOf(Dispatcher::class, $container->get(Dispatcher::class));
        self::assertInstanceOf(DispatcherContract::class, $container->get(DispatcherContract::class));
    }
}
