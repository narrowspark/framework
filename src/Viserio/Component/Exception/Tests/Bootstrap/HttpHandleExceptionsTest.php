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

namespace Viserio\Component\Exception\Tests\Bootstrap;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Bootstrap\InitializeContainerBootstrap;
use Viserio\Component\Exception\Bootstrap\HttpHandleExceptionsBootstrap;
use Viserio\Contract\Container\CompiledContainer as ContainerContract;
use Viserio\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class HttpHandleExceptionsTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(128, HttpHandleExceptionsBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_AFTER, HttpHandleExceptionsBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(InitializeContainerBootstrap::class, HttpHandleExceptionsBootstrap::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $handler = Mockery::mock(HttpHandlerContract::class);
        $handler->shouldReceive('register')
            ->once();

        $container = Mockery::mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->once()
            ->with(HttpHandlerContract::class)
            ->andReturn($handler);

        $kernel = Mockery::mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        HttpHandleExceptionsBootstrap::bootstrap($kernel);
    }
}
