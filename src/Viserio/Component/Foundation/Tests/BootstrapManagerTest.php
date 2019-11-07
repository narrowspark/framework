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

namespace Viserio\Component\Foundation\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernelBootstrap;
use Viserio\Component\Foundation\BootstrapManager;
use Viserio\Contract\Container\CompiledContainer as ContainerContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

/**
 * @internal
 *
 * @small
 */
final class BootstrapManagerTest extends MockeryTestCase
{
    public function testBootstrapWith(): void
    {
        $container = Mockery::mock(ContainerContract::class);
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

        $boot = new BootstrapManager($kernel);

        self::assertFalse($boot->hasBeenBootstrapped());

        $boot->bootstrapWith([ConfigureKernelBootstrap::class]);

        self::assertTrue($boot->hasBeenBootstrapped());
    }

    public function testAfterAndBeforeBootstrap(): void
    {
        $_SERVER['test'] = 0;

        $container = Mockery::mock(ContainerContract::class);
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

        $boot = new BootstrapManager($kernel);

        $boot->addBeforeBootstrapping(ConfigureKernelBootstrap::class, static function (): void {
            $_SERVER['test'] = 1;
        });

        $boot->addAfterBootstrapping(ConfigureKernelBootstrap::class, static function (): void {
            $_SERVER['test'] = 3;
        });

        $boot->bootstrapWith([ConfigureKernelBootstrap::class]);

        self::assertTrue($boot->hasBeenBootstrapped());
        self::assertSame(3, $_SERVER['test']);

        unset($_SERVER['test']);
    }
}
