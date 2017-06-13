<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\RegisterStaticalProxys;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;

class RegisterStaticalProxysTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $provider = new ConfigureLoggingServiceProvider();

        $bootstraper = new RegisterStaticalProxys();

        $container = $this->mock(ContainerContract::class);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('getKernelConfigurations')
            ->once()
            ->andReturn(['aliases' => ['test' => RegisterStaticalProxys::class]]);

        $bootstraper->bootstrap($kernel);
    }
}
