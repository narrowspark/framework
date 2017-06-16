<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\HandleLogger;
use Viserio\Component\Foundation\Provider\ConfigureLoggingServiceProvider;
use Viserio\Component\Log\Provider\LoggerServiceProvider;

class HandleLoggerTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $bootstraper = new HandleLogger();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('register')
            ->once()
            ->with(ConfigureLoggingServiceProvider::class);
        $container->shouldReceive('register')
            ->once()
            ->with(LoggerServiceProvider::class);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        $bootstraper->bootstrap($kernel);
    }
}
