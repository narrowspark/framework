<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Foundation\Bootstrap\LoadConfiguration;

class LoadConfigurationTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $bootstraper = new LoadConfiguration();
        $config      = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('patch.cached.config')
            ->andReturn('');
        $config->shouldReceive('get')
            ->once()
            ->with('path.config')
            ->andReturn(__DIR__ . '/../Fixtures/Config/');
        $config->shouldReceive('import')
            ->once()
            ->with(realpath(__DIR__ . '/../Fixtures/Config/app.php'));
        $config->shouldReceive('get')
            ->once()
            ->with('app.timezone', 'UTC')
            ->andReturn('UTC');

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $app->shouldReceive('detectEnvironment')
            ->once()
            ->andReturn('production');

        $bootstraper->bootstrap($app);
    }

    public function testBootstrapWithCachedData()
    {
        $bootstraper = new LoadConfiguration();
        $config      = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('patch.cached.config')
            ->andReturn(__DIR__ . '/../Fixtures/Config/app.php');
        $config->shouldReceive('setArray')
            ->once()
            ->with([]);
        $config->shouldReceive('get')
            ->never()
            ->with('path.config');
        $config->shouldReceive('import')
            ->never();
        $config->shouldReceive('get')
            ->once()
            ->with('app.timezone', 'UTC')
            ->andReturn('UTC');

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $app->shouldReceive('detectEnvironment')
            ->once()
            ->andReturn('production');

        $bootstraper->bootstrap($app);
    }
}
