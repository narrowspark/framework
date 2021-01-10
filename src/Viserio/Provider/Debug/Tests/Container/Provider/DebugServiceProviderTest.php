<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Provider\Debug\Tests\Container\Provider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\VarDumper\Cloner\ClonerInterface;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Symfony\Component\VarDumper\VarDumper;
use Viserio\Bridge\Twig\Extension\DumpExtension;
use Viserio\Component\Config\Container\Provider\ConfigServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Provider\Debug\Container\Provider\DebugServiceProvider;
use Viserio\Provider\Debug\HtmlDumper;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class DebugServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testProvider(): void
    {
        self::assertInstanceOf(VarDumper::class, $this->container->get(VarDumper::class));
        self::assertInstanceOf(DataDumperInterface::class, $this->container->get(DataDumperInterface::class));
        self::assertInstanceOf(DataDumperInterface::class, $this->container->get(HtmlDumper::class));
        self::assertInstanceOf(ClonerInterface::class, $this->container->get(ClonerInterface::class));
        self::assertInstanceOf(ClonerInterface::class, $this->container->get(VarCloner::class));
        self::assertFalse($this->container->has(DumpExtension::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new ConfigServiceProvider());
        $containerBuilder->register(new DebugServiceProvider());
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
