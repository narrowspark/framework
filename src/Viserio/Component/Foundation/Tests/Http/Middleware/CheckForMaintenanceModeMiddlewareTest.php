<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http\Middleware;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\Contract\Foundation\HttpKernel as HttpKernelContract;
use Viserio\Component\Foundation\Http\Middleware\CheckForMaintenanceModeMiddleware;

class CheckForMaintenanceModeMiddlewareTest extends MockeryTestCase
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

        self::assertInstanceOf(
            ResponseInterface::class,
            $middleware->process($server, $handler)
        );
    }

    /**
     * @expectedException \Viserio\Component\Foundation\Http\Exception\MaintenanceModeException
     * @expectedExceptionMessage test
     */
    public function testProcessWithMaintenance(): void
    {
        $server = $this->mock(ServerRequestInterface::class);
        $kernel = $this->mock(HttpKernelContract::class);
        $kernel->shouldReceive('isDownForMaintenance')
            ->once()
            ->andReturn(true);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('framework/down')
            ->andReturn(__DIR__ . '/../../Fixtures/Middleware/framework/down');

        $handler = $this->mock(RequestHandlerInterface::class);

        $middleware = new CheckForMaintenanceModeMiddleware($kernel);

        $middleware->process($server, $handler);
    }
}
