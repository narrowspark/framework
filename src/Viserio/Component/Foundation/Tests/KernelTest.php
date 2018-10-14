<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\EnvironmentDetector;
use Viserio\Component\Foundation\Tests\Fixture\Provider\FixtureServiceProvider;

/**
 * @internal
 */
final class KernelTest extends MockeryTestCase
{
    /**
     * @var string
     */
    private $rootPath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->rootPath = \dirname(__DIR__);
    }

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

        static::assertFalse($kernel->isLocal());
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

        static::assertFalse($kernel->isRunningUnitTests());
    }

    public function testisRunningInConsole(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertTrue($kernel->isRunningInConsole());
    }

    public function testIsDownForMaintenance(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertFalse($kernel->isDownForMaintenance());
    }

    public function testGetAppPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'app',
            $kernel->getAppPath()
        );
        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'app' . \DIRECTORY_SEPARATOR . 'test',
            $kernel->getAppPath('test')
        );
    }

    public function testGetConfigPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'config',
            $kernel->getConfigPath()
        );
        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'config' . \DIRECTORY_SEPARATOR . 'test',
            $kernel->getConfigPath('test')
        );
    }

    public function testGetDatabasePath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'database',
            $kernel->getDatabasePath()
        );
        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'database' . \DIRECTORY_SEPARATOR . 'test',
            $kernel->getDatabasePath('test')
        );
    }

    public function testGetPublicPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'public',
            $kernel->getPublicPath()
        );
        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'public' . \DIRECTORY_SEPARATOR . 'test',
            $kernel->getPublicPath('test')
        );
    }

    public function testGetStoragePath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'storage',
            $kernel->getStoragePath()
        );
        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'storage' . \DIRECTORY_SEPARATOR . 'test',
            $kernel->getStoragePath('test')
        );
    }

    public function testGetResourcePath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'resources',
            $kernel->getResourcePath()
        );
        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'resources' . \DIRECTORY_SEPARATOR . 'test',
            $kernel->getResourcePath('test')
        );
    }

    public function testGetLangPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'resources' . \DIRECTORY_SEPARATOR . 'lang',
            $kernel->getLangPath()
        );
    }

    public function testGetRoutesPath(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame(
            $this->rootPath . \DIRECTORY_SEPARATOR . 'routes',
            $kernel->getRoutesPath()
        );
    }

    public function testEnvironmentPathAndFile(): void
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        static::assertSame($this->rootPath, $kernel->getEnvironmentPath());

        $kernel->useEnvironmentPath(\DIRECTORY_SEPARATOR . 'test');

        static::assertSame(\DIRECTORY_SEPARATOR . 'test', $kernel->getEnvironmentPath());

        static::assertSame('.env', $kernel->getEnvironmentFile());

        $kernel->loadEnvironmentFrom('.test');

        static::assertSame('.test', $kernel->getEnvironmentFile());

        static::assertSame(\DIRECTORY_SEPARATOR . 'test' . \DIRECTORY_SEPARATOR . '.test', $kernel->getEnvironmentFilePath());
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

        static::assertSame([], $kernel->registerServiceProviders());

        $kernel->setConfigPath(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture');

        static::assertSame([FixtureServiceProvider::class], $kernel->registerServiceProviders());
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

        static::assertSame('prod', $kernel->detectEnvironment(function () {
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
