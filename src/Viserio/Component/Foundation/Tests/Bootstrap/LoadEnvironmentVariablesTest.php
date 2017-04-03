<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;

class LoadEnvironmentVariablesTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $bootstraper = new LoadEnvironmentVariables();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->with('patch.cached.config')
            ->andReturn('');

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $app->shouldReceive('environmentFile')
            ->once()
            ->andReturn('.env');
        $app->shouldReceive('environmentPath')
            ->once()
            ->andReturn('');

        $bootstraper->bootstrap($app);
    }

    public function testBootstrapWithAppEnv()
    {
        putenv('APP_ENV=production');

        $bootstraper = new LoadEnvironmentVariables();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->with('patch.cached.config')
            ->andReturn('');

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);
        $app->shouldReceive('environmentPath')
            ->twice()
            ->andReturn(__DIR__ . '/../Fixtures/');
        $app->shouldReceive('environmentFile')
            ->twice()
            ->andReturn('.env');
        $app->shouldReceive('loadEnvironmentFrom')
            ->once()
            ->with('.env.production');

        $bootstraper->bootstrap($app);

        // remove APP_ENV
        putenv('APP_ENV=');
        putenv('APP_ENV');
    }
}
