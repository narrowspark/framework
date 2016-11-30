<?php
declare(strict_types=1);
namespace Viserio\Bus\Tests\Providers;

use Viserio\Bus\Providers\QueueingBusServiceProvider;
use Viserio\Bus\QueueingDispatcher;
use Viserio\Container\Container;
use Viserio\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;

class QueueingBusServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new QueueingBusServiceProvider());

        self::assertInstanceOf(QueueingDispatcher::class, $container->get(QueueingDispatcher::class));
        self::assertInstanceOf(QueueingDispatcherContract::class, $container->get(QueueingDispatcherContract::class));
    }
}
