<?php
declare(strict_types=1);
namespace Viserio\Component\StaticalProxy\Tests\Provider;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\StaticalProxy\Provider\StaticalProxyServiceProvider;
use Viserio\Component\StaticalProxy\StaticalProxy;
use Viserio\Component\StaticalProxy\StaticalProxyResolver;

class StaticalProxyServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new StaticalProxyServiceProvider());

        self::assertInstanceOf(StaticalProxyResolver::class, $container->get(StaticalProxyResolver::class));
        self::assertInstanceOf(StaticalProxy::class, $container->get(StaticalProxy::class));
        self::assertInstanceOf(StaticalProxyResolver::class, $container->get('staticalproxy.resolver'));
        self::assertInstanceOf(StaticalProxy::class, $container->get('staticalproxy'));
        self::assertInstanceOf(StaticalProxy::class, $container->get('facade'));
    }
}
