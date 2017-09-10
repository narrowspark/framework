<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Bus\Dispatcher;
use Viserio\Component\Bus\Provider\BusServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Bus\Dispatcher as DispatcherContract;

class BusServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new BusServiceProvider());

        self::assertInstanceOf(Dispatcher::class, $container->get(Dispatcher::class));
        self::assertInstanceOf(DispatcherContract::class, $container->get(DispatcherContract::class));
        self::assertInstanceOf(DispatcherContract::class, $container->get('bus'));
    }
}
