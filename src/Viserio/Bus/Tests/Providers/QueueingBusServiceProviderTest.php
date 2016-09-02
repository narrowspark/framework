<?php
declare(strict_types=1);
namespace Viserio\Bus\Tests\Providers;

use Viserio\Container\Container;
use Viserio\Bus\QueueingDispatcher;
use Viserio\Contracts\Bus\QueueingDispatcher as QueueingDispatcherContract;
use Viserio\Bus\Providers\QueueingBusServiceProvider;

class QueueingBusServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new QueueingBusServiceProvider());

        $this->assertInstanceOf(QueueingDispatcher::class, $container->get(QueueingDispatcher::class));
        $this->assertInstanceOf(QueueingDispatcherContract::class, $container->get(QueueingDispatcherContract::class));
    }
}
