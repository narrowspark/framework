<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\Proxy;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Container\Proxy\ProxyFactory;
use Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy;

class ProxyFactoryTest extends TestCase
{
    public function testShouldCreateLazyProxies(): void
    {
        $factory     = new ProxyFactory(false);
        $instance    = new ClassToProxy();
        $initialized = false;
        $initializer = function (&$wrappedObject, $proxy, $method, $parameters, &$initializer) use ($instance, &$initialized) {
            $wrappedObject = $instance;
            $initializer   = null; // turning off further lazy initialization
            $initialized   = true;

            return true;
        };

        /** @var ClassToProxy $proxy */
        $proxy = $factory->createProxy(ClassToProxy::class, $initializer);

        self::assertFalse($initialized);
        self::assertInstanceOf(ClassToProxy::class, $proxy);

        $proxy->foo();

        self::assertTrue($initialized);
        self::assertSame($instance, $proxy->getInstance());
    }
}
