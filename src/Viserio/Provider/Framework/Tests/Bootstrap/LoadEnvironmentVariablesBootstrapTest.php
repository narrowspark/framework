<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Closure;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariablesBootstrap;
use Viserio\Contract\Foundation\Kernel as KernelContract;

/**
 * @internal
 *
 * @small
 */
final class LoadEnvironmentVariablesBootstrapTest extends MockeryTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['SHELL_VERBOSITY'], $_GET['SHELL_VERBOSITY'], $_SERVER['SHELL_VERBOSITY']);
    }

    public function testGetPriority(): void
    {
        self::assertSame(32, LoadEnvironmentVariablesBootstrap::getPriority());
    }

    public function testBootstrap(): void
    {
        $kernel = Mockery::mock(KernelContract::class);

        $kernel->shouldReceive('getEnvironmentFile')
            ->once()
            ->andReturn('.env.local');
        $kernel->shouldReceive('getEnvironmentPath')
            ->once()
            ->andReturn(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture');

        $this->arrangeIsRunningInConsole($kernel);

        $this->arrangeKernelDetect($kernel);

        LoadEnvironmentVariablesBootstrap::bootstrap($kernel);
    }

    public function testBootstrapWithAppEnv(): void
    {
        \putenv('APP_ENV=prod');

        $kernel = Mockery::mock(KernelContract::class);

        $this->arrangeEnvPathToFixtures($kernel);

        $kernel->shouldReceive('getEnvironmentFile')
            ->twice()
            ->andReturn('.env');
        $kernel->shouldReceive('loadEnvironmentFrom')
            ->once()
            ->with('.env.prod');

        $this->arrangeKernelDetect($kernel);
        $this->arrangeIsRunningInConsole($kernel);

        LoadEnvironmentVariablesBootstrap::bootstrap($kernel);

        // remove APP_ENV
        \putenv('APP_ENV=');
        \putenv('APP_ENV');
    }

    public function testBootstrapWithArgv(): void
    {
        $argv = $_SERVER['argv'];

        $_SERVER['argv'] = [
            'load',
            '--env=local',
        ];

        $kernel = Mockery::mock(KernelContract::class);

        $this->arrangeEnvPathToFixtures($kernel);

        $kernel->shouldReceive('getEnvironmentFile')
            ->twice()
            ->andReturn('.env');

        $kernel->shouldReceive('loadEnvironmentFrom')
            ->once()
            ->with('.env.local');
        $kernel->shouldReceive('isRunningInConsole')
            ->once()
            ->andReturn(true);

        $this->arrangeKernelDetect($kernel);

        LoadEnvironmentVariablesBootstrap::bootstrap($kernel);

        $_SERVER['argv'] = $argv;
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Contract\Foundation\Kernel $kernel
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
     * @param \Mockery\MockInterface|\Viserio\Contract\Foundation\Kernel $kernel
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
     * @param \Mockery\MockInterface|\Viserio\Contract\Foundation\Kernel $kernel
     */
    private function arrangeKernelDetect($kernel): void
    {
        $kernel->shouldReceive('detectEnvironment')
            ->once()
            ->with(Mockery::type(Closure::class));

        $kernel->shouldReceive('detectDebugMode')
            ->once()
            ->with(Mockery::type(Closure::class));
    }
}
