<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\HttpHandleExceptions;

class HttpHandleExceptionsTest extends MockeryTestCase
{
    public function testBootstrap(): void
    {
        $bootstraper = new HttpHandleExceptions();

        $handler = $this->mock(HttpHandlerContract::class);
        $handler->shouldReceive('register')
            ->once();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->once()
            ->with(HttpHandlerContract::class)
            ->andReturn($handler);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        $bootstraper->bootstrap($kernel);
    }
}
