<?php
declare(strict_types=1);
namespace Viserio\Bus\Tests\Providers;

use Viserio\Bus\Dispatcher;
use Viserio\Bus\Providers\BusServiceProvider;
use Viserio\Container\Container;
use Viserio\Contracts\Bus\Dispatcher as DispatcherContract;
use PHPUnit\Framework\TestCase;

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
