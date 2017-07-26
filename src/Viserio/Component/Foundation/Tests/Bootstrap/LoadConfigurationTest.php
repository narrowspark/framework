<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadConfiguration;

class LoadConfigurationTest extends MockeryTestCase
{
    public function testBootstrap(): void
    {
        $bootstraper = new LoadConfiguration();
        $config      = $this->mock(RepositoryContract::class);
        $config->shouldReceive('import')
            ->once()
            ->with(dirname(__DIR__) . '/Fixtures/Config/app.php');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.timezone', 'UTC')
            ->andReturn('UTC');

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('register')
            ->once()
            ->with(Mockery::type(ConfigServiceProvider::class));
        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('detectEnvironment')
            ->once()
            ->andReturn('production');
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('config.cache')
            ->andReturn('');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->andReturn(dirname(__DIR__) . '/Fixtures/Config');

        $bootstraper->bootstrap($kernel);
    }

    public function testBootstrapWithCachedData(): void
    {
        $bootstraper = new LoadConfiguration();
        $config      = $this->mock(RepositoryContract::class);
        $config->shouldReceive('setArray')
            ->once()
            ->with([]);
        $config->shouldReceive('import')
            ->never();
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.timezone', 'UTC')
            ->andReturn('UTC');

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('register')
            ->once()
            ->with(Mockery::type(ConfigServiceProvider::class));
        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('detectEnvironment')
            ->once()
            ->andReturn('production');
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('config.cache')
            ->andReturn(__DIR__ . '/../Fixtures/Config/app.php');
        $kernel->shouldReceive('getConfigPath')
            ->never();

        $bootstraper->bootstrap($kernel);
    }
}
