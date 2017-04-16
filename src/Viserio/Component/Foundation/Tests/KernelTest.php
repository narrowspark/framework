<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Container\Container;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Contracts\Translation\TranslationManager as TranslationManagerContract;
use Viserio\Component\Foundation\AbstractKernel;
use Viserio\Component\Foundation\Bootstrap\LoadEnvironmentVariables;
use Viserio\Component\Foundation\Events\LocaleChangedEvent;
use Viserio\Component\Contracts\Foundation\Environment as EnvironmentContract;
use Viserio\Component\Foundation\EnvironmentDetector;

class KernelTest extends MockeryTestCase
{
    public function testKernelBootAndBootstrap()
    {
        $container = new Container();

        $kernel = $this->getKernel($container);

        $kernel->boot();

        // dont do a second call
        $kernel->boot();

        $kernel->bootstrapWith([
            LoadEnvironmentVariables::class,
        ]);

        self::assertTrue($kernel->hasBeenBootstrapped());
        self::assertTrue($kernel->isBooted());
    }

    public function testSetLocaleSetsLocaleAndFiresLocaleChangedEvent()
    {
        $container = new Container();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('set')
            ->once()
            ->with('viserio.app.locale', 'foo');
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.locale', 'en')
            ->andReturn('foo');

        $container->instance(RepositoryContract::class, $config);

        $trans = $this->mock(TranslationManagerContract::class);
        $trans->shouldReceive('setLocale')
            ->once()
            ->with('foo');

        $container->instance(TranslationManagerContract::class, $trans);

        $events = $this->mock(EventManagerContract::class);
        $events->shouldReceive('trigger')
            ->once()
            ->with(Mock::type(LocaleChangedEvent::class));

        $container->instance(EventManagerContract::class, $events);

        $kernel = $this->getKernel($container);

        self::assertInstanceOf(KernelContract::class, $kernel->setLocale('foo'));
        self::assertSame('foo', $kernel->getLocale());
    }

    public function testGetFallbackLocale()
    {
        $container = new Container();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.fallback_locale', 'en')
            ->andReturn('en');

        $container->instance(RepositoryContract::class, $config);

        $kernel = $this->getKernel($container);

        self::assertSame('en', $kernel->getFallbackLocale());
    }

    public function testIsLocal()
    {
        $container = new Container();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.env')
            ->andReturn('prod');

        $container->instance(RepositoryContract::class, $config);

        $kernel = $this->getKernel($container);

        self::assertFalse($kernel->isLocal());
    }

    public function testIsRunningUnitTests()
    {
        $container = new Container();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('viserio.app.env')
            ->andReturn('prod');

        $container->instance(RepositoryContract::class, $config);

        $kernel = $this->getKernel($container);

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

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('set')
            ->once()
            ->with('viserio.app.env', 'prod');

        $container->instance(RepositoryContract::class, $config);

        $kernel = $this->getKernel($container);

        self::assertSame('prod', $kernel->detectEnvironment(function() {
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
        };
    }
}
