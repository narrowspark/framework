<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;

class ConfigureKernelTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $bootstraper = new ConfigureKernel();
        $container = $this->mock(ContainerContract::class);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('setKernelConfigurations')
            ->once()
            ->with($container);

        $bootstraper->bootstrap($kernel);
    }
}
