<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Http\Middlewares;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Foundation\Http\Middlewares\CheckForMaintenanceModeMiddleware;

class CheckForMaintenanceModeMiddlewareTest extends MockeryTestCase
{
    public function testProcess()
    {
        $server = $this->mock(ServerRequestInterface::class);
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('app.maintenance', false)
            ->andReturn(false);
        $delegate = $this->mock(DelegateInterface::class);
        $delegate->shouldReceive('process')
            ->once()
            ->with($server)
            ->andReturn($this->mock(ResponseInterface::class));

        $middleware = new CheckForMaintenanceModeMiddleware($config);

        self::assertInstanceOf(
            ResponseInterface::class,
            $middleware->process($server, $delegate)
        );
    }

    /**
     * @expectedException \Viserio\Component\Foundation\Http\Exceptions\MaintenanceModeException
     * @expectedExceptionMessage test
     */
    public function testProcessWithMaintenance()
    {
        $server = $this->mock(ServerRequestInterface::class);
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('app.maintenance', false)
            ->andReturn(true);
        $config->shouldReceive('get')
            ->once()
            ->with('path.storage')
            ->andReturn(__DIR__ . '/../../Fixtures/Middleware');
        $delegate = $this->mock(DelegateInterface::class);

        $middleware = new CheckForMaintenanceModeMiddleware($config);

        $middleware->process($server, $delegate);
    }
}
