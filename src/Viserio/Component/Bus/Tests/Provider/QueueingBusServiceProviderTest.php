<?php
declare(strict_types=1);
namespace Viserio\Component\Bus\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Bus\Provider\QueueingBusServiceProvider;
use Viserio\Component\Bus\QueueingDispatcher;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Bus\QueueingDispatcher as QueueingDispatcherContract;

/**
 * @internal
 */
final class QueueingBusServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new QueueingBusServiceProvider());

        $this->assertInstanceOf(QueueingDispatcher::class, $container->get(QueueingDispatcher::class));
        $this->assertInstanceOf(QueueingDispatcherContract::class, $container->get(QueueingDispatcherContract::class));
        $this->assertInstanceOf(QueueingDispatcherContract::class, $container->get('bus'));
    }
}
