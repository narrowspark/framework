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

namespace Viserio\Provider\Framework\Tests\Bootstrap;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Provider\Framework\Bootstrap\ConfigureKernelBootstrap;
use Viserio\Provider\Framework\Bootstrap\InitializeContainerBootstrap;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
        $container->shouldReceive('getParameter')
            ->with('viserio.app.timezone')
            ->andReturn('UTC');
        $container->shouldReceive('getParameter')
            ->with('viserio.app.charset')
            ->andReturn('UTF-8');

        $kernel = Mockery::mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        ConfigureKernelBootstrap::bootstrap($kernel);
    }
}
