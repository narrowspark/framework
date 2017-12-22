<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Exception\Handler as HandlerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\HandleExceptions;

class HandleExceptionsTest extends MockeryTestCase
{
    public function testBootstrap(): void
    {
        $bootstraper = new HandleExceptions();

        $handler = $this->mock(HandlerContract::class);
        $handler->shouldReceive('register')
            ->once();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->once()
            ->with(HandlerContract::class)
            ->andReturn($handler);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        $bootstraper->bootstrap($kernel);
    }
}
