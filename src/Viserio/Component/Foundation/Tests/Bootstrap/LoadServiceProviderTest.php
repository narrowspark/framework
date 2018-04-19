<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Tests\Fixtures\Provider\FixtureServiceProvider;

class LoadServiceProviderTest extends MockeryTestCase
{
    public function testBootstrap(): void
    {
        $provider    = new FixtureServiceProvider();
        $bootstraper = new LoadServiceProvider();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('resolve')
            ->once()
            ->with(FixtureServiceProvider::class)
            ->andReturn($provider);
        $container->shouldReceive('register')
            ->once()
            ->with($provider);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('getEnvironment')
            ->once()
            ->andReturn('prod');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('serviceproviders.php')
            ->andReturn(__DIR__ . '/../Fixtures/serviceproviders.php');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('prod/serviceproviders.php')
            ->andReturn(__DIR__ . '/../Fixtures/prod/serviceproviders.php');

        $bootstraper->bootstrap($kernel);
    }

    public function testBootstrapWithFileNotFound(): void
    {
        $provider    = new FixtureServiceProvider();
        $bootstraper = new LoadServiceProvider();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('resolve')
            ->never()
            ->with(FixtureServiceProvider::class)
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
