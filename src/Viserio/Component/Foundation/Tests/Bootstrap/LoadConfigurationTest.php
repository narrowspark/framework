<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Mockery;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadConfiguration;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class LoadConfigurationTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\Config\Repository
     */
    private $configMock;

    /**
     * @var string
     */
    private $appConfigPath;

    /**
     * @var \Viserio\Component\Foundation\Bootstrap\LoadConfiguration
     */
    private $bootstrap;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->configMock    = $this->mock(RepositoryContract::class);
        $this->bootstrap     = new LoadConfiguration();
        $this->appConfigPath = self::normalizeDirectorySeparator(\dirname(__DIR__) . '/Fixtures/Config');
    }

    public function testBootstrap(): void
    {
        $this->configMock->shouldReceive('import')
            ->once()
            ->with($this->appConfigPath . '/app.php');
        $this->configMock->shouldReceive('import')
            ->once()
            ->with($this->appConfigPath . '/prod/app.php');

        $this->arrangeTimezone();

        $container = $this->arrangeContainerWithConfig();

        $kernel = $this->arrangeKernel($container);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('config.cache')
            ->andReturn('');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->andReturn($this->appConfigPath);
        $kernel->shouldReceive('getEnvironment')
            ->once()
            ->andReturn('prod');
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('prod')
            ->andReturn($this->appConfigPath . '/prod');

        $this->bootstrap->bootstrap($kernel);
    }

    public function testBootstrapWithCachedData(): void
    {
        $this->configMock->shouldReceive('setArray')
            ->once()
            ->with([]);
        $this->configMock->shouldReceive('import')
            ->never();

        $this->arrangeTimezone();

        $container = $this->arrangeContainerWithConfig();

        $kernel = $this->arrangeKernel($container);
        $kernel->shouldReceive('getStoragePath')
            ->once()
            ->with('config.cache')
            ->andReturn($this->appConfigPath . '/app.php');
        $kernel->shouldReceive('getConfigPath')
            ->never();

        $this->bootstrap->bootstrap($kernel);
    }

    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    private function arrangeTimezone(): void
    {
        $this->configMock->shouldReceive('get')
            ->once()
            ->with('viserio.app.timezone', 'UTC')
            ->andReturn('UTC');
    }

    /**
     * @return \Mockery\MockInterface
     */
    private function arrangeContainerWithConfig(): MockInterface
    {
        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('register')
            ->once()
            ->with(Mockery::type(ConfigServiceProvider::class));
        $container->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($this->configMock);

        return $container;
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     *
     * @return Mockery\MockInterface
     */
    private function arrangeKernel($container): MockInterface
    {
        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('detectEnvironment')
            ->once()
            ->andReturn('prod');

        return $kernel;
    }
}
