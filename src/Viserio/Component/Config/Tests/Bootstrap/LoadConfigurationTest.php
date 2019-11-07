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

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\Bootstrap\ConfigurationLoaderBootstrap;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProviderBootstrap;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Definition\ObjectDefinition as ObjectDefinitionContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

/**
 * @internal
 *
 * @small
 */
final class LoadConfigurationTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\Container\Definition\ObjectDefinition */
    private $definitionMock;

    /** @var string */
    private $appConfigPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->definitionMock = Mockery::mock(ObjectDefinitionContract::class);
        $this->appConfigPath = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'LoadConfiguration';
    }

    public function testGetPriority(): void
    {
        self::assertSame(64, ConfigurationLoaderBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_AFTER, ConfigurationLoaderBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(LoadServiceProviderBootstrap::class, ConfigurationLoaderBootstrap::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $packagesPath = $this->appConfigPath . \DIRECTORY_SEPARATOR . 'packages' . \DIRECTORY_SEPARATOR;

        $this->definitionMock->shouldReceive('addMethodCall')
            ->once()
            ->with('set', ['viserio.app.env', 'prod']);
        $this->definitionMock->shouldReceive('addMethodCall')
            ->once()
            ->with('import', [$packagesPath . 'route.php']);
        $this->definitionMock->shouldReceive('addMethodCall')
            ->once()
            ->with('import', [$this->appConfigPath . \DIRECTORY_SEPARATOR . 'app.php']);
        $this->definitionMock->shouldReceive('addMethodCall')
            ->once()
            ->with('import', [$this->appConfigPath . \DIRECTORY_SEPARATOR . 'prod' . \DIRECTORY_SEPARATOR . 'app.php']);
        $this->definitionMock->shouldReceive('addMethodCall')
            ->once()
            ->with('import', [$packagesPath . 'prod' . \DIRECTORY_SEPARATOR . 'route.php']);

        $containerMock = $this->mock(ContainerBuilderContract::class);
        $containerMock->shouldReceive('findDefinition')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($this->definitionMock);

        $kernelMock = $this->arrangeKernel($containerMock);

        $kernelMock->shouldReceive('getConfigPath')
            ->once()
            ->withNoArgs()
            ->andReturn($this->appConfigPath);
        $kernelMock->shouldReceive('getConfigPath')
            ->once()
            ->with('packages')
            ->andReturn($this->appConfigPath . \DIRECTORY_SEPARATOR . 'packages');
        $kernelMock->shouldReceive('getEnvironment')
            ->times(3)
            ->andReturn('prod');
        $kernelMock->shouldReceive('getConfigPath')
            ->once()
            ->withNoArgs()
            ->with('prod')
            ->andReturn($this->appConfigPath . \DIRECTORY_SEPARATOR . 'prod');
        $kernelMock->shouldReceive('getConfigPath')
            ->once()
            ->with('packages' . \DIRECTORY_SEPARATOR . 'prod')
            ->andReturn($this->appConfigPath . \DIRECTORY_SEPARATOR . 'packages' . \DIRECTORY_SEPARATOR . 'prod');

        ConfigurationLoaderBootstrap::bootstrap($kernelMock);
    }

    /**
     * {@inheritdoc}
     */
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
        $kernel = Mockery::mock(KernelContract::class);

        $kernel->shouldReceive('getContainerBuilder')
            ->once()
            ->andReturn($container);

        return $kernel;
    }
}
