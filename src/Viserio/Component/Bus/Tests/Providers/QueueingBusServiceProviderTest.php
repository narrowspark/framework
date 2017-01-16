<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests\Providers;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Bus\Providers\QueueingBusServiceProvider;
use Viserio\Component\Bus\QueueingDispatcher;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;

class QueueingBusServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new QueueingBusServiceProvider());

        self::assertInstanceOf(QueueingDispatcher::class, $container->get(QueueingDispatcher::class));
        self::assertInstanceOf(QueueingDispatcherContract::class, $container->get(QueueingDispatcherContract::class));
    }
}
