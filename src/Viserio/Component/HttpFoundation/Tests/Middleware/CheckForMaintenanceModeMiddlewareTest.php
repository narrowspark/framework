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

namespace Viserio\Component\HttpFoundation\Tests\Middleware;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\HttpFoundation\Exception\MaintenanceModeException;
use Viserio\Component\HttpFoundation\Middleware\CheckForMaintenanceModeMiddleware;
use Viserio\Contract\HttpFoundation\HttpKernel as HttpKernelContract;

/**
 * @internal
 *
 * @small
 */
final class CheckForMaintenanceModeMiddlewareTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        $server = Mockery::mock(ServerRequestInterface::class);
        $config = Mockery::mock(HttpKernelContract::class);
        $config->shouldReceive('isDownForMaintenance')
            ->once()
            ->andReturn(false);
        $handler = Mockery::mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with($server)
            ->andReturn(Mockery::mock(ResponseInterface::class));

        $middleware = new CheckForMaintenanceModeMiddleware($config);

        self::assertInstanceOf(
            ResponseInterface::class,
            $middleware->process($server, $handler)
        );
    }

    public function testProcessWithMaintenance(): void
    {
        $this->expectException(MaintenanceModeException::class);
        $this->expectExceptionMessage('test');

        $server = Mockery::mock(ServerRequestInterface::class);
        $kernel = Mockery::mock(HttpKernelContract::class);
        $kernel->shouldReceive('isDownForMaintenance')
            ->once()
            ->andReturn(true);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('framework' . \DIRECTORY_SEPARATOR . 'down')
            ->andReturn(\dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Middleware' . \DIRECTORY_SEPARATOR . 'framework' . \DIRECTORY_SEPARATOR . 'down');

        $handler = Mockery::mock(RequestHandlerInterface::class);

        $middleware = new CheckForMaintenanceModeMiddleware($kernel);

        $middleware->process($server, $handler);
    }
}
