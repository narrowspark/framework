<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Tests\Middleware;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\HttpFoundation\Exception\MaintenanceModeException;
use Viserio\Component\HttpFoundation\Middleware\CheckForMaintenanceModeMiddleware;

/**
 * @internal
 */
final class CheckForMaintenanceModeMiddlewareTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        $server = $this->mock(ServerRequestInterface::class);
        $config = $this->mock(HttpKernelContract::class);
        $config->shouldReceive('isDownForMaintenance')
            ->once()
            ->andReturn(false);
        $handler = $this->mock(RequestHandlerInterface::class);
        $handler->shouldReceive('handle')
            ->once()
            ->with($server)
            ->andReturn($this->mock(ResponseInterface::class));

        $middleware = new CheckForMaintenanceModeMiddleware($config);

        $this->assertInstanceOf(
            ResponseInterface::class,
            $middleware->process($server, $handler)
        );
    }

    public function testProcessWithMaintenance(): void
    {
        $this->expectException(MaintenanceModeException::class);
        $this->expectExceptionMessage('test');

        $server = $this->mock(ServerRequestInterface::class);
        $kernel = $this->mock(HttpKernelContract::class);
        $kernel->shouldReceive('isDownForMaintenance')
            ->once()
            ->andReturn(true);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('framework' . \DIRECTORY_SEPARATOR . 'down')
            ->andReturn(\dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Middleware' . \DIRECTORY_SEPARATOR . 'framework' . \DIRECTORY_SEPARATOR . 'down');

        $handler = $this->mock(RequestHandlerInterface::class);

        $middleware = new CheckForMaintenanceModeMiddleware($kernel);

        $middleware->process($server, $handler);
    }
}
