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

namespace Viserio\Component\Config\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\Bootstrap\ConfigurationLoaderBootstrap;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Pipeline\ExtendedDefinitionPipe;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProviderBootstrap;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

/**
 * @internal
 *
 * @small
 */
final class LoadConfigurationTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\Config\Repository */
    private $configMock;

    /** @var string */
    private $appConfigPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configMock = \Mockery::mock(RepositoryContract::class);
        $this->appConfigPath = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'LoadConfiguration';
    }

    public function testGetPriority(): void
    {
        self::assertSame(32, ConfigurationLoaderBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_BEFORE, ConfigurationLoaderBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(LoadServiceProviderBootstrap::class, ConfigurationLoaderBootstrap::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $packagesPath = $this->appConfigPath . \DIRECTORY_SEPARATOR . 'packages' . \DIRECTORY_SEPARATOR;

        $container = new ContainerBuilder();
        $container->singleton(RepositoryContract::class, $this->configMock);

        $kernel = $this->arrangeKernel($container);

        ConfigurationLoaderBootstrap::bootstrap($kernel);

        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('framework' . \DIRECTORY_SEPARATOR . 'config.cache.php')
            ->andReturn('');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->withNoArgs()
            ->andReturn($this->appConfigPath);
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('packages')
            ->andReturn($this->appConfigPath . \DIRECTORY_SEPARATOR . 'packages');
        $kernel->shouldReceive('getEnvironment')
            ->times(3)
            ->andReturn('prod');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->withNoArgs()
            ->with('prod')
            ->andReturn($this->appConfigPath . \DIRECTORY_SEPARATOR . 'prod');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('packages' . \DIRECTORY_SEPARATOR . 'prod')
            ->andReturn($this->appConfigPath . \DIRECTORY_SEPARATOR . 'packages' . \DIRECTORY_SEPARATOR . 'prod');

        (new ExtendedDefinitionPipe())->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(RepositoryContract::class);

        self::assertSame(['set', ['viserio.app.env', 'prod'],  false], $definition->getMethodCalls()[0]);
        self::assertSame(['import', [$packagesPath . 'route.php'], false], $definition->getMethodCalls()[1]);
        self::assertSame(['import', [$this->appConfigPath . \DIRECTORY_SEPARATOR . 'app.php'], false], $definition->getMethodCalls()[2]);
        self::assertSame(['import', [$this->appConfigPath . \DIRECTORY_SEPARATOR . 'prod' . \DIRECTORY_SEPARATOR . 'app.php'], false], $definition->getMethodCalls()[3]);
        self::assertSame(['import', [$packagesPath . 'prod' . \DIRECTORY_SEPARATOR . 'route.php'], false], $definition->getMethodCalls()[4]);
    }

    public function testBootstrapWithCachedData(): void
    {
        $container = new ContainerBuilder();
        $container->singleton(RepositoryContract::class, $this->configMock);

        $kernel = $this->arrangeKernel($container);

        ConfigurationLoaderBootstrap::bootstrap($kernel);

        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('framework' . \DIRECTORY_SEPARATOR . 'config.cache.php')
            ->andReturn($this->appConfigPath . \DIRECTORY_SEPARATOR . 'app.php');
        $kernel->shouldReceive('getConfigPath')
            ->never();

        (new ExtendedDefinitionPipe())->process($container);

        /** @var \Viserio\Contract\Container\Definition\ObjectDefinition $definition */
        $definition = $container->getDefinition(RepositoryContract::class);

        self::assertSame(['setArray', [[], true], false], $definition->getMethodCalls()[0]);
        self::assertFalse($definition->hasMethodCall('import'));
    }

    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Contract\Container\ContainerBuilder $container
     *
     * @return \Mockery\MockInterface|\Viserio\Contract\Foundation\Kernel
     */
    private function arrangeKernel(ContainerBuilderContract $container)
    {
        $kernel = \Mockery::mock(KernelContract::class);

        $kernel->shouldReceive('getContainerBuilder')
            ->once()
            ->andReturn($container);

        return $kernel;
    }
}
