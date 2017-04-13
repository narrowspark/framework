<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;

class LoadServiceProviderTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $provider = new ConfigureLoggingServiceProvider();

        $bootstraper = new LoadServiceProvider();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.serviceproviders', [])
            ->andReturn([ConfigureLoggingServiceProvider::class]);

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $container->shouldReceive('make')
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

        $bootstraper->bootstrap($kernel);
    }
}
