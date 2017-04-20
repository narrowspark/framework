<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\EnvironmentDetector;
use Viserio\Component\Foundation\Events\BootstrappedEvent;
use Viserio\Component\Foundation\Events\BootstrappingEvent;

class KernelTest extends MockeryTestCase
{
    public function testKernelBootAndBootstrap()
    {
        $container = new Container();

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappedEvent::class));
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(BootstrappingEvent::class));

        $container->instance(EventManagerContract::class, $events);

        $kernel = $this->getKernel($container);
        $kernel->setConfigurations([
            'viserio' => [
                'app' => [
                    'env' => 'prod',
                ],
            ],
        ]);

        $kernel->bootstrapWith([
            LoadEnvironmentVariables::class,
        ]);

        self::assertTrue($kernel->hasBeenBootstrapped());
    }

    public function testIsLocal()
    {
        $container = new Container();

        $kernel = $this->getKernel($container);
        $kernel->setConfigurations([
            'viserio' => [
                'app' => [
                    'env' => 'prod',
                ],
            ],
        ]);

        self::assertFalse($kernel->isLocal());
    }

    public function testIsRunningUnitTests()
    {
        $container = new Container();

        $kernel = $this->getKernel($container);
        $kernel->setConfigurations([
            'viserio' => [
                'app' => [
                    'env' => 'prod',
                ],
            ],
        ]);

        self::assertFalse($kernel->isRunningUnitTests());
    }

    public function testisRunningInConsole()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertTrue($kernel->isRunningInConsole());
    }

    public function testIsDownForMaintenance()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertFalse($kernel->isDownForMaintenance());
    }

    public function testGetAppPath()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('/app', $kernel->getAppPath());
        self::assertSame('/app/test', $kernel->getAppPath('test'));
    }

    public function testGetConfigPath()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('/config', $kernel->getConfigPath());
        self::assertSame('/config/test', $kernel->getConfigPath('test'));
    }

    public function testGetDatabasePath()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('/database', $kernel->getDatabasePath());
        self::assertSame('/database/test', $kernel->getDatabasePath('test'));
    }

    public function testGetPublicPath()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('/public', $kernel->getPublicPath());
        self::assertSame('/public/test', $kernel->getPublicPath('test'));
    }

    public function testGetStoragePath()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('/storage', $kernel->getStoragePath());
        self::assertSame('/storage/test', $kernel->getStoragePath('test'));
    }

    public function testGetResourcePath()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('/resources', $kernel->getResourcePath());
        self::assertSame('/resources/test', $kernel->getResourcePath('test'));
    }

    public function testGetLangPath()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('/resources/lang', $kernel->getLangPath());
    }

    public function testGetRoutesPath()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('/routes', $kernel->getRoutesPath());
    }

    public function testEnvironmentPathAndFile()
    {
        $kernel = $this->getKernel($this->mock(ContainerContract::class));

        self::assertSame('', $kernel->getEnvironmentPath());

        $kernel->useEnvironmentPath('/test');

        self::assertSame('/test', $kernel->getEnvironmentPath());

        self::assertSame('.env', $kernel->getEnvironmentFile());

        $kernel->loadEnvironmentFrom('.test');

        self::assertSame('.test', $kernel->getEnvironmentFile());

        self::assertSame('/test/.test', $kernel->getEnvironmentFilePath());
    }

    public function testDetectEnvironment()
    {
        $container = new Container();
        $container->singleton(EnvironmentContract::class, EnvironmentDetector::class);

        $kernel = $this->getKernel($container);
        $kernel->setConfigurations([
            'viserio' => [
                'app' => [
                    'env' => 'prod',
                ],
            ],
        ]);

        self::assertSame('prod', $kernel->detectEnvironment(function () {
            return 'prod';
        }));
    }

    protected function getKernel($container = null)
    {
        return new class($container) extends AbstractKernel {
            public function __construct($container)
            {
                $this->container = $container;
            }

            protected function initializeContainer(): void
            {
            }

            public function bootstrap(): void
            {
            }
        };
    }
}
