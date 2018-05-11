<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\EnvironmentDetector;
use Viserio\Component\Foundation\Tests\Fixtures\Provider\FixtureServiceProvider;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class KernelTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testIsLocal(): void
    {
        $container = new Container();

        $kernel = $this->getKernel($container);
        $kernel->setKernelConfigurations([
            'viserio' => [
                'app' => [
                    'env'   => 'prod',
                    'debug' => true,
                ],
            ],
        ]);

        self::assertFalse($kernel->isLocal());
    }

    public function testIsRunningUnitTests(): void
    {
        $container = new Container();

        $kernel = $this->getKernel($container);
        $kernel->setKernelConfigurations([
            'viserio' => [
                'app' => [
                    'env'   => 'prod',
                    'debug' => true,
                ],
            ],
        ]);

        self::assertFalse($kernel->isRunningUnitTests());
    }

    public function testisRunningInConsole(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertTrue($kernel->isRunningInConsole());
    }

    public function testIsDownForMaintenance(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertFalse($kernel->isDownForMaintenance());
    }

    public function testGetAppPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/app'),
            self::normalizeDirectorySeparator($kernel->getAppPath())
        );
        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/app/test'),
            self::normalizeDirectorySeparator($kernel->getAppPath('test'))
        );
    }

    public function testGetConfigPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/config'),
            self::normalizeDirectorySeparator($kernel->getConfigPath())
        );
        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/config/test'),
            self::normalizeDirectorySeparator($kernel->getConfigPath('test'))
        );
    }

    public function testGetDatabasePath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/database'),
            self::normalizeDirectorySeparator($kernel->getDatabasePath())
        );
        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/database/test'),
            self::normalizeDirectorySeparator($kernel->getDatabasePath('test'))
        );
    }

    public function testGetPublicPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/public'),
            self::normalizeDirectorySeparator($kernel->getPublicPath())
        );
        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/public/test'),
            self::normalizeDirectorySeparator($kernel->getPublicPath('test'))
        );
    }

    public function testGetStoragePath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/storage'),
            self::normalizeDirectorySeparator($kernel->getStoragePath())
        );
        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/storage/test'),
            self::normalizeDirectorySeparator($kernel->getStoragePath('test'))
        );
    }

    public function testGetResourcePath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/resources'),
            self::normalizeDirectorySeparator($kernel->getResourcePath())
        );
        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/resources/test'),
            self::normalizeDirectorySeparator($kernel->getResourcePath('test'))
        );
    }

    public function testGetLangPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/resources/lang'),
            self::normalizeDirectorySeparator($kernel->getLangPath())
        );
    }

    public function testGetRoutesPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__) . '/routes'),
            self::normalizeDirectorySeparator($kernel->getRoutesPath())
        );
    }

    public function testEnvironmentPathAndFile(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame(
            self::normalizeDirectorySeparator(\dirname(__DIR__)),
            self::normalizeDirectorySeparator($kernel->getEnvironmentPath())
        );

        $kernel->useEnvironmentPath('/test');

        self::assertSame('/test', $kernel->getEnvironmentPath());

        self::assertSame('.env', $kernel->getEnvironmentFile());

        $kernel->loadEnvironmentFrom('.test');

        self::assertSame('.test', $kernel->getEnvironmentFile());

        self::assertSame('/test/.test', $kernel->getEnvironmentFilePath());
    }

    public function testRegisterServiceProviders(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));
        $kernel->setKernelConfigurations([
            'viserio' => [
                'app' => [
                    'env'   => 'prod',
                    'debug' => false,
                ],
            ],
        ]);

        self::assertSame([], $kernel->registerServiceProviders());

        $kernel->setConfigPath(__DIR__ . '/Fixtures');

        self::assertSame([FixtureServiceProvider::class], $kernel->registerServiceProviders());
    }

    public function testDetectEnvironment(): void
    {
        $container = new Container();
        $container->singleton(EnvironmentContract::class, EnvironmentDetector::class);

        $kernel = $this->getKernel($container);
        $kernel->setKernelConfigurations([
            'viserio' => [
                'app' => [
                    'env'   => 'prod',
                    'debug' => false,
                ],
            ],
        ]);

        self::assertSame('prod', $kernel->detectEnvironment(function () {
            return 'prod';
        }));
    }

    protected function getKernel($container)
    {
        return new class($container) extends AbstractKernel {
            private $configPath;
            private $testContainer;

            public function __construct($container)
            {
                $this->testContainer = $container;

                parent::__construct();
            }

            public function setConfigPath(string $path): void
            {
                $this->configPath = $path;
            }

            public function getConfigPath(string $path = ''): string
            {
                if ($this->configPath !== null) {
                    return $this->configPath . ($path ? '/' . $path : $path);
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
             * {@inheritdoc}
             */
            protected function initializeContainer(): ContainerContract
            {
                return $this->testContainer;
            }

            /**
             * {@inheritdoc}
             */
            protected function registerBaseServiceProviders(): void
            {
            }

            /**
             * {@inheritdoc}
             */
            protected function registerBaseBindings(): void
            {
            }
        };
    }
}
