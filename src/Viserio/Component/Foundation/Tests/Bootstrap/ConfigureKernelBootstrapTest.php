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

namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Bootstrap\InitializeContainerBootstrap;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernelBootstrap;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

/**
 * @internal
 *
 * @small
 */
final class ConfigureKernelBootstrapTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(32, ConfigureKernelBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_AFTER, ConfigureKernelBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(InitializeContainerBootstrap::class, ConfigureKernelBootstrap::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $container = Mockery::mock(CompiledContainerContract::class);
        $container->shouldReceive('get')
            ->with('config')
            ->andReturn([]);

        $kernel = Mockery::mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('setKernelConfigurations')
            ->once()
            ->with([]);

        ConfigureKernelBootstrap::bootstrap($kernel);
    }
}
