<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\UnitTest\Instantiator;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ProxyManager\Proxy\LazyLoadingInterface;
use ProxyManager\Proxy\ValueHolderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Container\Definition\ObjectDefinition;
use Viserio\Component\Container\LazyProxy\Instantiator\RealServiceInstantiator;
use Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy;
use Viserio\Component\Contract\Container\Types;

/**
 * @internal
 */
final class RealServiceInstantiatorTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Container\LazyProxy\Instantiator\RealServiceInstantiator
     */
    protected $instantiator;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->instantiator = new RealServiceInstantiator();
    }

    public function testInstantiateProxy(): void
    {
        $container   = $this->mock(ContainerInterface::class);
        $definition  = new ObjectDefinition('test', ClassToProxy::class, Types::PLAIN);
        $definition->setLazy(true);
        $definition->setInstantiator($this->instantiator);

        $definition->resolve($container);

        $proxy = $definition->getValue();

        $this->assertFalse($proxy->initialized);
        $this->assertNotInstanceOf(LazyLoadingInterface::class, $proxy);
        $this->assertNotInstanceOf(ValueHolderInterface::class, $proxy);
        $this->assertInstanceOf(ClassToProxy::class, $proxy);
    }
}
