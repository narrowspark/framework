<?php
declare(strict_types=1);
namespace Viserio\StaticalProxy\Tests\Providers;

use Viserio\Container\Container;
use Viserio\StaticalProxy\Providers\StaticalProxyServiceProvider;
use Viserio\StaticalProxy\StaticalProxy;
use Viserio\StaticalProxy\StaticalProxyResolver;

class StaticalProxyServiceProviderTest extends \PHPUnit_Framework_TestCase
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
