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

namespace Viserio\Component\Exception\Tests\Bootstrap;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Bootstrap\InitializeContainerBootstrap;
use Viserio\Component\Exception\Bootstrap\ConsoleHandleExceptionsBootstrap;
use Viserio\Contract\Container\CompiledContainer as ContainerContract;
use Viserio\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

/**
 * @internal
 *
 * @small
 */
final class ConsoleHandleExceptionsTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(128, ConsoleHandleExceptionsBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_AFTER, ConsoleHandleExceptionsBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(InitializeContainerBootstrap::class, ConsoleHandleExceptionsBootstrap::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $handler = Mockery::mock(ConsoleHandlerContract::class);
        $handler->shouldReceive('register')
            ->once();

        $container = Mockery::mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->once()
            ->with(ConsoleHandlerContract::class)
            ->andReturn($handler);

        $kernel = Mockery::mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        ConsoleHandleExceptionsBootstrap::bootstrap($kernel);
    }
}
