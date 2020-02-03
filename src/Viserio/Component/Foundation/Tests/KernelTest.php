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

namespace Viserio\Component\Foundation\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Tests\Fixture\Provider\FixtureServiceProvider;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;

/**
 * @internal
 *
 * @preserveGlobalState disabled
 *
 * @small
 */
final class KernelTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\Container\CompiledContainer */
    private $containerMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->containerMock = Mockery::mock(CompiledContainerContract::class);
    }

    public function testIsLocal(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertFalse($kernel->isLocal());
    }

    public function testIsDebug(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertFalse($kernel->isDebug());
    }

    public function testIsRunningUnitTests(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertFalse($kernel->isRunningUnitTests());
    }

    public function testisRunningInConsole(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertTrue($kernel->isRunningInConsole());
    }

    public function testIsDownForMaintenance(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertFalse($kernel->isDownForMaintenance());
    }

    public function testGetAppPath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'app', $kernel->getAppPath());
        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'app' . \DIRECTORY_SEPARATOR . 'test', $kernel->getAppPath('test'));
    }

    public function testGetConfigPath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'config', $kernel->getConfigPath());
        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'test', $kernel->getConfigPath('test'));
    }

    public function testGetDatabasePath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'database', $kernel->getDatabasePath());
        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'database' . \DIRECTORY_SEPARATOR . 'test', $kernel->getDatabasePath('test'));
    }

    public function testGetPublicPath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'public', $kernel->getPublicPath());
        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'public' . \DIRECTORY_SEPARATOR . 'test', $kernel->getPublicPath('test'));
    }

    public function testGetStoragePath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'storage', $kernel->getStoragePath());
        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'storage' . \DIRECTORY_SEPARATOR . 'test', $kernel->getStoragePath('test'));
    }

    public function testGetResourcePath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'resources', $kernel->getResourcePath());
        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'resources' . \DIRECTORY_SEPARATOR . 'test', $kernel->getResourcePath('test'));
    }

    public function testGetLangPath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'resources' . \DIRECTORY_SEPARATOR . 'lang', $kernel->getLangPath());
    }

    public function testGetRoutesPath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'routes', $kernel->getRoutesPath());
    }

    public function testEnvironmentPathAndFile(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__), $kernel->getEnvironmentPath());

        $kernel->useEnvironmentPath(\DIRECTORY_SEPARATOR . 'test');

        self::assertSame(\DIRECTORY_SEPARATOR . 'test', $kernel->getEnvironmentPath());

        self::assertSame('.env', $kernel->getEnvironmentFile());

        $kernel->loadEnvironmentFrom('.test');

        self::assertSame('.test', $kernel->getEnvironmentFile());

        self::assertSame(\DIRECTORY_SEPARATOR . 'test' . \DIRECTORY_SEPARATOR . '.test', $kernel->getEnvironmentFilePath());
    }

    public function testGetTestsPath(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'tests', $kernel->getTestsPath());
        self::assertSame(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'tests' . \DIRECTORY_SEPARATOR . 'test', $kernel->getTestsPath('test'));
    }

    public function testGetRegisteredServiceProviders(): void
    {
        $kernel = $this->getKernel($this->containerMock);
        $kernel->setEnv('local');

        self::assertSame([], $kernel->getRegisteredServiceProviders());

        $kernel->setConfigPath(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture');

        self::assertSame([FixtureServiceProvider::class], $kernel->getRegisteredServiceProviders());
    }

    public function testDetectEnvironment(): void
    {
        $kernel = $this->getKernel($this->containerMock);

        self::assertSame('prod', $kernel->detectEnvironment(static function () {
            return 'prod';
        }));
    }

    /**
     * @param \Viserio\Contract\Container\CompiledContainer $container
     *
     * @return AbstractKernel
     */
    protected function getKernel(CompiledContainerContract $container): AbstractKernel
    {
        return new class($container) extends AbstractKernel {
            private ?string $configPath = null;

            public function __construct($container)
            {
                $this->container = $container;

                parent::__construct();
            }

            public function setEnv(string $env): void
            {
                $this->environment = $env;
            }

            public function setConfigPath(string $path): void
            {
                $this->configPath = $path;
            }

            public function getConfigPath(string $path = ''): string
            {
                if ($this->configPath !== null) {
                    return $this->configPath . ($path ? \DIRECTORY_SEPARATOR . $path : $path);
                }

                return parent::getConfigPath($path);
            }

            /**
             * {@inheritdoc}
             */
            public function bootstrap(): void
            {
            }

            /**
             * Returns the bootstrap lock file path.
             *
             * @return string
             */
            protected function getBootstrapLockFileName(): string
            {
                return 'test.lock';
            }
        };
    }
}
