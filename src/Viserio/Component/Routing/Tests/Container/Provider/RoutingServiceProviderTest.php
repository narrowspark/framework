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

namespace Viserio\Component\Routing\Tests\Container\Provider;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriFactoryInterface;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Test\AbstractContainerTestCase;
use Viserio\Component\Routing\Container\Provider\RoutingServiceProvider;
use Viserio\Component\Routing\Generator\UrlGenerator;
use Viserio\Component\Routing\Router;
use Viserio\Contract\Routing\UrlGenerator as UrlGeneratorContract;

/**
 * @internal
 *
 * @small
 */
final class RoutingServiceProviderTest extends AbstractContainerTestCase
{
    use MockeryPHPUnitIntegration;

    protected const DUMP_CLASS_CONTAINER = false;

    public function testProvider(): void
    {
        $this->containerBuilder->singleton(UriFactoryInterface::class)
            ->setSynthetic(true);

        $this->prepareContainerBuilder($this->containerBuilder);

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        $this->container->set(ServerRequestInterface::class, Mockery::mock(ServerRequestInterface::class));
        $this->container->set(UriFactoryInterface::class, Mockery::mock(UriFactoryInterface::class));

        self::assertInstanceOf(Router::class, $this->container->get(Router::class));
        self::assertInstanceOf(UrlGeneratorContract::class, $this->container->get(UrlGeneratorContract::class));
        self::assertInstanceOf(UrlGeneratorContract::class, $this->container->get(UrlGenerator::class));
        self::assertInstanceOf(Router::class, $this->container->get('router'));
    }

    public function testGetUrlGeneratorProvider(): void
    {
        $this->prepareContainerBuilder($this->containerBuilder);

        $this->containerBuilder->compile();

        $this->dumpContainer(__FUNCTION__);

        $this->container->set(ServerRequestInterface::class, Mockery::mock(ServerRequestInterface::class));

        self::assertFalse($this->container->has(UrlGeneratorContract::class));
        self::assertFalse($this->container->has(UrlGenerator::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->singleton(ServerRequestInterface::class)
            ->setSynthetic(true);

        $containerBuilder->register(new RoutingServiceProvider());
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
