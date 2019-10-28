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
use Viserio\Component\Config\Bootstrap\LoadServiceProviderOptionsBootstrap;
use Viserio\Component\Config\Tests\Fixture\ServiceProvider\DefaultProviderWithDimensions;
use Viserio\Component\Config\Tests\Fixture\ServiceProvider\DefaultProviderWithoutDimensions;
use Viserio\Component\Config\Tests\Fixture\ServiceProvider\OverwriteForDefaultProviderWithDimensions;
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
final class LoadServiceProviderOptionsBootstrapTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(32, LoadServiceProviderOptionsBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_AFTER, LoadServiceProviderOptionsBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(LoadServiceProviderBootstrap::class, LoadServiceProviderOptionsBootstrap::getBootstrapper());
    }

    /**
     * @dataProvider provideBootstrapCases
     *
     * @param array $providers
     * @param array $expected
     */
    public function testBootstrap(array $providers, array $expected): void
    {
        $kernelMock = $this->mock(KernelContract::class);
        $kernelMock->shouldReceive('getRegisteredServiceProviders')
            ->once()
            ->andReturn($providers);

        $containerBuilderMock = $this->mock(ContainerBuilderContract::class);

        $definitionMock = $this->mock(ObjectDefinitionContract::class);
        $definitionMock->shouldReceive('addMethodCall')
            ->once()
            ->with('setArray', [$expected]);

        $containerBuilderMock
            ->shouldReceive('findDefinition')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($definitionMock);

        $kernelMock->shouldReceive('getContainerBuilder')
            ->once()
            ->andReturn($containerBuilderMock);

        LoadServiceProviderOptionsBootstrap::bootstrap($kernelMock);
    }

    public function provideBootstrapCases(): iterable
    {
        return [
            [[DefaultProviderWithoutDimensions::class], DefaultProviderWithoutDimensions::getDefaultOptions()],
            [[DefaultProviderWithoutDimensions::class, DefaultProviderWithDimensions::class], \array_merge(DefaultProviderWithoutDimensions::getDefaultOptions(), ['narrowspark' => DefaultProviderWithDimensions::getDefaultOptions()])],
            [[DefaultProviderWithDimensions::class, OverwriteForDefaultProviderWithDimensions::class], \array_merge(['narrowspark' => DefaultProviderWithDimensions::getDefaultOptions()], ['narrowspark' => OverwriteForDefaultProviderWithDimensions::getDefaultOptions()])],
        ];
    }
}
