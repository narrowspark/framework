<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;

/**
 * @internal
 */
final class LoadEnvironmentVariablesTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        static::assertSame(32, LoadEnvironmentVariables::getPriority());
    }

    public function testDontLoadIfCached(): void
    {
        $kernel = $this->mock(KernelContract::class);

        $this->arrangeStoragePath($kernel, __DIR__ . '/../Fixture/config.cache.php');

        $kernel->shouldReceive('getEnvironmentFile')
            ->never();
        $kernel->shouldReceive('getEnvironmentPath')
            ->never();

        LoadEnvironmentVariables::bootstrap($kernel);
    }

    public function testBootstrap(): void
    {
        $kernel = $this->mock(KernelContract::class);

        $this->arrangeStoragePath($kernel, '');

        $kernel->shouldReceive('getEnvironmentFile')
            ->once()
            ->andReturn('.env');
        $kernel->shouldReceive('getEnvironmentPath')
            ->once()
            ->andReturn('');

        $this->arrangeIsRunningInConsole($kernel);

        LoadEnvironmentVariables::bootstrap($kernel);
    }

    public function testBootstrapWithAppEnv(): void
    {
        \putenv('APP_ENV=prod');

        $kernel = $this->mock(KernelContract::class);

        $this->arrangeEnvPathToFixtures($kernel);

        $kernel->shouldReceive('getEnvironmentFile')
            ->twice()
            ->andReturn('.env');
        $kernel->shouldReceive('loadEnvironmentFrom')
            ->once()
            ->with('.env.prod');

        $this->arrangeStoragePath($kernel, '');
        $this->arrangeIsRunningInConsole($kernel);

        LoadEnvironmentVariables::bootstrap($kernel);

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

        LoadEnvironmentVariables::bootstrap($kernel);

        foreach (['load', '--env=local'] as $i => $value) {
            if (($key = \array_search($value, $_SERVER['argv'], true)) !== false) {
                unset($_SERVER['argv'][$key]);
            }
        }
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Foundation\Kernel $kernel
     *
     * @return void
     */
    private function arrangeEnvPathToFixtures($kernel): void
    {
        $kernel->shouldReceive('getEnvironmentPath')
            ->twice()
            ->andReturn(__DIR__ . '/../Fixture/');
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Foundation\Kernel $kernel
     *
     * @return void
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
            ->with('config.cache.php')
            ->andReturn($path);
    }
}
