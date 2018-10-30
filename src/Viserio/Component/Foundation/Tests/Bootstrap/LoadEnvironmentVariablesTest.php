<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Dotenv\Dotenv;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\Tests\Helper\ClassStack;

/**
 * @internal
 */
final class LoadEnvironmentVariablesTest extends MockeryTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        ClassStack::reset();
    }

    public function testGetPriority(): void
    {
        $this->assertSame(32, LoadEnvironmentVariables::getPriority());
    }

    public function testDontLoadIfCached(): void
    {
        $kernel = $this->mock(KernelContract::class);

        $this->arrangeStoragePath($kernel, \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'config.cache.php');

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
            ->andReturn('.env.local');
        $kernel->shouldReceive('getEnvironmentPath')
            ->once()
            ->andReturn(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture');

        $this->arrangeIsRunningInConsole($kernel);

        $kernel->shouldReceive('detectEnvironment')
            ->once()
            ->with(\Mockery::type(\Closure::class));

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

    public function testBootstrapWithOutDotenvAndEnv(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('[APP_ENV] environment variable is not defined. You need to define environment variables for configuration or run [composer require vlucas/phpdotenv] as a Composer dependency to load variables from a .env file.');

        ClassStack::add(Dotenv::class, false);

        \putenv('APP_ENV=');
        \putenv('APP_ENV');

        $kernel = $this->mock(KernelContract::class);

        $this->arrangeStoragePath($kernel, '');

        LoadEnvironmentVariables::bootstrap($kernel);
    }

    public function testBootstrapWithOutDotenv(): void
    {
        ClassStack::add(Dotenv::class, false);

        \putenv('APP_ENV=prod');

        $kernel = $this->mock(KernelContract::class);

        $this->arrangeStoragePath($kernel, '');

        $kernel->shouldReceive('detectEnvironment')
            ->once()
            ->with(\Mockery::type(\Closure::class));

        LoadEnvironmentVariables::bootstrap($kernel);

        \putenv('APP_ENV=');
        \putenv('APP_ENV');
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
            ->andReturn(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR);
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
