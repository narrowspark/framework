<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Container\Tests\IntegrationTest;

use ProxyManager\Proxy\LazyLoadingInterface;
use stdClass;
use Viserio\Component\Container\LazyProxy\ProxyDumper;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy;
use Viserio\Contract\Container\Definition\ObjectDefinition;
use Viserio\Contract\Container\ServiceProvider\ContainerBuilder as ContainerBuilderContract;

/**
 * @internal
 *
 * @small
 */
final class ContainerLazyObjectTest extends AbstractContainerTestCase
{
    protected const DUMP_CLASS_CONTAINER = false;

    protected const SKIP_TEST_PIPE = true;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->proxyDumper = new ProxyDumper();
    }

    /**
     * @runInSeparateProcess
     */
    public function testDumpContainerWithProxyServiceWillShareProxies(): void
    {
        $this->containerBuilder->singleton('proxy', ClassToProxy::class)
            ->setLazy(true)
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->dumpContainer(\ucfirst(__FUNCTION__));

        /** @var \ProxyManager\Proxy\VirtualProxyInterface|\Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy $proxy */
        $proxy = $this->container->get('proxy');

        $proxy->__destruct();

        self::assertSame(0, $proxy::$destructorCount);

        self::assertSame($proxy, $this->container->get('proxy'), 'The same proxy is retrieved on multiple subsequent calls');
        self::assertInstanceOf(ClassToProxy::class, $proxy);
        self::assertInstanceOf(LazyLoadingInterface::class, $proxy);
        self::assertFalse($proxy->isProxyInitialized());

        $proxy->initializeProxy();

        self::assertSame($proxy, $this->container->get('proxy'), 'The same proxy is retrieved after initialization');
        self::assertTrue($proxy->isProxyInitialized());
        self::assertInstanceOf(ClassToProxy::class, $proxy->getWrappedValueHolderValue());
        self::assertNotInstanceOf(LazyLoadingInterface::class, $proxy->getWrappedValueHolderValue());

        $proxy->__destruct();

        self::assertSame(1, $proxy::$destructorCount);
    }

    public function testGenerateOneLazyProxyForSameClass(): void
    {
        $this->containerBuilder->bind('foo', stdClass::class)
            ->setLazy(true)
            ->setPublic(true);
        $this->containerBuilder->bind('bar', stdClass::class)
            ->setLazy(true)
            ->setPublic(true);

        $this->containerBuilder->compile();

        $this->dumpContainer($functionName = __FUNCTION__);

        self::assertInstanceOf(stdClass::class, $this->container->get('foo'));
        self::assertInstanceOf(stdClass::class, $this->container->get('bar'));

        $className = $this->getDumperContainerClassName($functionName);
        $dirPath = \rtrim($this->getDumpFolderPath(), \DIRECTORY_SEPARATOR) . \DIRECTORY_SEPARATOR;

        self::assertRegExp('/stdClass_7505e02fc61bdbec8ef82430242d0041c28f8477d8fc1e70135fd3b6d304a641/', \file_get_contents($dirPath . $className . '.php'));
    }

    public function testCreateExtendedLazyObject(): void
    {
        $this->containerBuilder->bind('foo', ClassToProxy::class)
            ->setPublic(true)
            ->setLazy(true);
        $this->containerBuilder->extend('foo', static function (ObjectDefinition $definition, ContainerBuilderContract $containerBuilder) {
            $definition->addMethodCall('setBar', ['test']);

            return $definition;
        });

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        /** @var \ProxyManager\Proxy\VirtualProxyInterface|\Viserio\Component\Container\Tests\Fixture\Proxy\ClassToProxy $proxy */
        $proxy = $this->container->get('foo');

        self::assertSame('test', $proxy->bar);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Compiled' . \DIRECTORY_SEPARATOR;
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
