<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;

/**
 * @internal
 */
final class ConfigureKernelTest extends MockeryTestCase
{
    public function testBootstrap(): void
    {
        $bootstraper = new ConfigureKernel();

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->with('config')
            ->andReturn([]);

        $kernel = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('setKernelConfigurations')
            ->once()
            ->with([]);

        $bootstraper->bootstrap($kernel);
    }
}
