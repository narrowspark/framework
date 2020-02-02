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

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Throwable;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariablesBootstrap;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Webmozart\Assert\Assert;

/**
 * @internal
 *
 * @covers \Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariablesBootstrap
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
        $_SERVER['APP_ENV'] = 'prod';
        $_SERVER['APP_DEBUG'] = true;

        /** @var \Mockery\MockInterface|\Viserio\Contract\Foundation\Kernel $kernel */
        $kernel = $this->mock(KernelContract::class);

        $kernel->shouldReceive('detectEnvironment')
            ->once()
            ->withArgs(static function ($value) {
                try {
                    Assert::same('prod', $value());
                } catch (Throwable $exception) {
                    return false;
                }

                return true;
            });

        $kernel->shouldReceive('detectDebugMode')
            ->once()
            ->withArgs(static function ($value) {
                try {
                    Assert::true($value());
                } catch (Throwable $exception) {
                    return false;
                }

                return true;
            });

        LoadEnvironmentVariablesBootstrap::bootstrap($kernel);

        // remove env
        \putenv('APP_ENV=');
        \putenv('APP_ENV');
        \putenv('APP_DEBUG=');
        \putenv('APP_DEBUG');
    }

    public function testBootstrapDetectEnvironmentThrowsExceptionOnMissingAppEnv(): void
    {
        /** @var \Mockery\MockInterface|\Viserio\Contract\Foundation\Kernel $kernel */
        $kernel = $this->mock(KernelContract::class);

        $kernel->shouldReceive('detectEnvironment')
            ->once()
            ->withArgs(static function ($callable) {
                try {
                    $callable();
                } catch (Throwable $exception) {
                    Assert::same('[APP_ENV] environment variable is not defined.', $exception->getMessage());

                    return true;
                }

                return false;
            });
        $kernel->shouldReceive('detectDebugMode')
            ->once()
            ->withArgs(static function ($callable) {
                try {
                    $callable();
                } catch (Throwable $exception) {
                    Assert::same('[APP_DEBUG] environment variable is not defined.', $exception->getMessage());

                    return true;
                }

                return false;
            });

        LoadEnvironmentVariablesBootstrap::bootstrap($kernel);
    }

    public function testBootstrapWithArgv(): void
    {
        $_SERVER['APP_ENV'] = 'prod';
        $_SERVER['APP_DEBUG'] = true;

        $argv = $_SERVER['argv'];

        $_SERVER['argv'] = [
            'load',
            '--env=local',
            '--no-debug',
        ];

        /** @var \Mockery\MockInterface|\Viserio\Contract\Foundation\Kernel $kernel */
        $kernel = new class() extends AbstractKernel {
            /**
             * {@inheritdoc}
             */
            protected function getBootstrapLockFileName(): string
            {
                return '';
            }
        };

        LoadEnvironmentVariablesBootstrap::bootstrap($kernel);

        self::assertSame('local', $kernel->getEnvironment());
        self::assertFalse($kernel->isDebug());

        $_SERVER['argv'] = $argv;
        // remove env
        \putenv('APP_ENV=');
        \putenv('APP_ENV');
        \putenv('APP_DEBUG=');
        \putenv('APP_DEBUG');
    }
}
