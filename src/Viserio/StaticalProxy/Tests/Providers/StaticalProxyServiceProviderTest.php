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

        $this->assertInstanceOf(StaticalProxyResolver::class, $container->get(StaticalProxyResolver::class));
        $this->assertInstanceOf(StaticalProxy::class, $container->get(StaticalProxy::class));
        $this->assertInstanceOf(StaticalProxyResolver::class, $container->get('staticalproxy.resolver'));
        $this->assertInstanceOf(StaticalProxy::class, $container->get('staticalproxy'));
        $this->assertInstanceOf(StaticalProxy::class, $container->get('facade'));
    }
}
