<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Instantiator;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\LazyProxy\Instantiator\RuntimeInstantiator;
use Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy;
use Viserio\Component\Contract\Container\Types;

/**
 * @internal
 */
final class RuntimeInstantiatorTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Container\LazyProxy\Instantiator\RuntimeInstantiator
     */
    protected $instantiator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->instantiator = new RuntimeInstantiator();
    }

    public function testInstantiateProxy(): void
    {
        $container   = $this->mock(ContainerInterface::class);
        $definition  = new ObjectDefinition('test', ClassToProxy::class, Types::PLAIN);
        $definition->setLazy(true);
        $definition->setInstantiator($this->instantiator);

        $definition->resolve($container);

        /** @var \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|\Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy $proxy */
        $proxy = $definition->getValue();

        $this->assertFalse($proxy->initialized);
        $this->assertInstanceOf(LazyLoadingInterface::class, $proxy);
        $this->assertInstanceOf(ValueHolderInterface::class, $proxy);
        $this->assertInstanceOf(ClassToProxy::class, $proxy);

        $proxy->initialize();

        $this->assertTrue($proxy->initialized);
    }
}
