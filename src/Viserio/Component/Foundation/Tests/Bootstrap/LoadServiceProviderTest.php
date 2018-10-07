<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;
use Viserio\Component\Foundation\Tests\Fixture\Provider\FixtureServiceProvider;

/**
 * @internal
 */
final class LoadServiceProviderTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Foundation\Tests\Fixture\Provider\FixtureServiceProvider
     */
    private $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->provider  = new FixtureServiceProvider();
    }

    public function testBootstrap(): void
    {
        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('resolve')
            ->once()
            ->with(FixtureServiceProvider::class)
            ->andReturn($this->provider);
        $container->shouldReceive('register')
            ->once()
            ->with($this->provider);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('registerServiceProviders')
            ->andReturn(require \dirname(__DIR__) . '/Fixture/serviceproviders.php');

        LoadServiceProvider::bootstrap($kernel);
    }
}
