<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Provider\ConfigureLoggingServiceProvider;

class LoadServiceProviderTest extends MockeryTestCase
{
    public function testBootstrap(): void
    {
        $provider    = new ConfigureLoggingServiceProvider();
        $bootstraper = new LoadServiceProvider();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('resolve')
            ->once()
            ->with(ConfigureLoggingServiceProvider::class)
            ->andReturn($provider);
        $container->shouldReceive('register')
            ->once()
            ->with($provider);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('/serviceproviders.php')
            ->andReturn(__DIR__ . '/../Fixtures/serviceproviders.php');

        $bootstraper->bootstrap($kernel);
    }

    public function testBootstrapWithFileNotFound(): void
    {
        $provider    = new ConfigureLoggingServiceProvider();
        $bootstraper = new LoadServiceProvider();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('resolve')
            ->never()
            ->with(ConfigureLoggingServiceProvider::class)
            ->andReturn($provider);
        $container->shouldReceive('register')
            ->never()
            ->with($provider);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('/serviceproviders.php')
            ->andReturn('serviceproviders.php');

        $bootstraper->bootstrap($kernel);
    }
}
