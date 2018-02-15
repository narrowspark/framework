<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;

class LoadEnvironmentVariablesTest extends MockeryTestCase
{
    public function testDontLoadIfCached(): void
    {
        $bootstraper = new LoadEnvironmentVariables();

        $kernel = $this->mock(KernelContract::class);
        $this->arrangeStoragePath($kernel, __DIR__ . '/../Fixtures/Config/app.php');
        $kernel->shouldReceive('getEnvironmentFile')
            ->never();
        $kernel->shouldReceive('getEnvironmentPath')
            ->never();

        $bootstraper->bootstrap($kernel);
    }

    public function testBootstrap(): void
    {
        $bootstraper = new LoadEnvironmentVariables();

        $kernel = $this->mock(KernelContract::class);
        $this->arrangeStoragePath($kernel, '');
        $kernel->shouldReceive('getEnvironmentFile')
            ->once()
            ->andReturn('.env');
        $kernel->shouldReceive('getEnvironmentPath')
            ->once()
            ->andReturn('');
        $this->arrangeIsRunningInConsole($kernel);

        $bootstraper->bootstrap($kernel);
    }

    public function testBootstrapWithAppEnv(): void
    {
        \putenv('APP_ENV=production');

        $bootstraper = new LoadEnvironmentVariables();

        $kernel = $this->mock(KernelContract::class);
        $this->arrangeEnvPathToFixtures($kernel);
        $kernel->shouldReceive('getEnvironmentFile')
            ->twice()
            ->andReturn('.env');
        $kernel->shouldReceive('loadEnvironmentFrom')
            ->once()
            ->with('.env.production');
        $this->arrangeStoragePath($kernel, '');
        $this->arrangeIsRunningInConsole($kernel);

        $bootstraper->bootstrap($kernel);

        // remove APP_ENV
        \putenv('APP_ENV=');
        \putenv('APP_ENV');
    }

    public function testBootstrapWithArgv(): void
    {
        $_SERVER['argv'] = [
            'load',
            '--env=local',
        ];

        $bootstraper = new LoadEnvironmentVariables();

        $kernel = $this->mock(KernelContract::class);
        $this->arrangeEnvPathToFixtures($kernel);
        $kernel->shouldReceive('getEnvironmentFile')
            ->twice()
            ->andReturn('.env');
        $this->arrangeStoragePath($kernel, '');
        $kernel->shouldReceive('loadEnvironmentFrom')
            ->once()
            ->with('.env.local');
        $kernel->shouldReceive('isRunningInConsole')
            ->once()
            ->andReturn(true);

        $bootstraper->bootstrap($kernel);

        foreach (['load', '--env=local'] as $i => $value) {
            if (($key = \array_search($value, $_SERVER['argv'], true)) !== false) {
                unset($_SERVER['argv'][$key]);
            }
        }
    }

    /**
     * @param $kernel
     */
    private function arrangeEnvPathToFixtures($kernel): void
    {
        $kernel->shouldReceive('getEnvironmentPath')
            ->twice()
            ->andReturn(__DIR__ . '/../Fixtures/');
    }

    /**
     * @param $kernel
     */
    private function arrangeIsRunningInConsole($kernel): void
    {
        $kernel->shouldReceive('isRunningInConsole')
            ->once()
            ->andReturn(false);
    }

    /**
     * @param MockInterface $kernel
     * @param string        $path
     */
    private function arrangeStoragePath(MockInterface $kernel, string $path): void
    {
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('config.cache')
            ->andReturn($path);
    }
}
