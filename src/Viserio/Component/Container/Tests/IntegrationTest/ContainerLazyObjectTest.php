<?php
declare(strict_types=1);
namespace Viserio\Component\Container\Tests\IntegrationTest;

use ProxyManager\Proxy\LazyLoadingInterface;
use Viserio\Component\Container\Compiler\CompileHelper;
use Viserio\Component\Container\LazyProxy\Instantiator\RuntimeInstantiator;
use Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy;

/**
 * @internal
 */
final class ContainerLazyObjectTest extends BaseContainerTest
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->compiledContainerBuilder->setInstantiator(new RuntimeInstantiator());
    }

    public function testCreateLazyObjectWithContainer(): void
    {
        $this->compiledContainerBuilder->instance('proxy', ClassToProxy::class);
        $this->compiledContainerBuilder->setLazy('proxy');

        /** @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|\Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy */
        $proxy = $this->compiledContainerBuilder->get('proxy');

        $proxy->__destruct();

        static::assertSame(0, $proxy::$destructorCount);

        static::assertSame($proxy, $this->compiledContainerBuilder->get('proxy'), 'The same proxy is retrieved on multiple subsequent calls');
        static::assertInstanceOf(ClassToProxy::class, $proxy);
        static::assertInstanceOf(LazyLoadingInterface::class, $proxy);
        static::assertFalse($proxy->isProxyInitialized());

        $proxy->initializeProxy();

        static::assertSame($proxy, $this->compiledContainerBuilder->get('proxy'), 'The same proxy is retrieved after initialization');
        static::assertTrue($proxy->isProxyInitialized());
        static::assertInstanceOf(ClassToProxy::class, $proxy->getWrappedValueHolderValue());
        static::assertNotInstanceOf(LazyLoadingInterface::class, $proxy->getWrappedValueHolderValue());

        $proxy->__destruct();

        static::assertSame(1, $proxy::$destructorCount);
    }

    /**
     * @runInSeparateProcess
     */
    public function testCreateLazyObjectWithBuildContainer(): void
    {
        $this->compiledContainerBuilder->instance('proxy', ClassToProxy::class);
        $this->compiledContainerBuilder->setLazy('proxy');

        $container = $this->compiledContainerBuilder->build();

        /** @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|\Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy */
        $proxy = $container->get('proxy');

        $proxy->__destruct();

        static::assertSame(0, $proxy::$destructorCount);

        static::assertSame($proxy, $container->get('proxy'), 'The same proxy is retrieved on multiple subsequent calls');
        static::assertInstanceOf(ClassToProxy::class, $proxy);
        static::assertInstanceOf(LazyLoadingInterface::class, $proxy);
        static::assertFalse($proxy->isProxyInitialized());

        $proxy->initializeProxy();

        static::assertSame($proxy, $container->get('proxy'), 'The same proxy is retrieved after initialization');
        static::assertTrue($proxy->isProxyInitialized());
        static::assertInstanceOf(ClassToProxy::class, $proxy->getWrappedValueHolderValue());
        static::assertNotInstanceOf(LazyLoadingInterface::class, $proxy->getWrappedValueHolderValue());

        $proxy->__destruct();

        static::assertSame(1, $proxy::$destructorCount);
    }

    public function testGenerateOneLazyProxyForSameClass(): void
    {
        $this->compiledContainerBuilder->instance('foo', \stdClass::class);
        $this->compiledContainerBuilder->instance('bar', \stdClass::class);
        $this->compiledContainerBuilder->setLazy('foo');
        $this->compiledContainerBuilder->setLazy('bar');

        $container = $this->compiledContainerBuilder->build();
        $container->get('foo');
        $container->get('bar');

        $className = \stdClass::class;
        $dir       = static::COMPILATION_DIR . \DIRECTORY_SEPARATOR;

        static::assertFileExists($dir . $className . '_' . \md5($className . CompileHelper::SALT) . '.php');
        static::assertCount(1, \glob($dir . $className . '_*'));
    }

    public function testCreateExtendedLazyObjectWithContainer(): void
    {
        $functionName = __FUNCTION__;

        $this->compiledContainerBuilder->instance($functionName, ClassToProxy::class);
        $this->compiledContainerBuilder->extend($functionName, function ($container, $p) {
            $p->setBar('test');

            return $p;
        });
        $this->compiledContainerBuilder->setLazy($functionName);

        /** @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|\Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy */
        $proxy = $this->compiledContainerBuilder->get($functionName);

        static::assertSame('test', $proxy->bar);
    }

    public function testCreateExtendedLazyObjectWithBuildContainer(): void
    {
        $this->compiledContainerBuilder->instance('proxy', ClassToProxy::class);
        $this->compiledContainerBuilder->extend('proxy', function ($container, $p) {
            $p->setBar('test');

            return $p;
        });
        $this->compiledContainerBuilder->setLazy('proxy');

        $container = $this->compiledContainerBuilder->build();

        /** @var $proxy \ProxyManager\Proxy\LazyLoadingInterface|\ProxyManager\Proxy\ValueHolderInterface|\Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy */
        $proxy = $container->get('proxy');

        static::assertSame('test', $proxy->bar);
    }
}
