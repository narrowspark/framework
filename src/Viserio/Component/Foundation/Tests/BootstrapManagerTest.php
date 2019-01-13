<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;
use Viserio\Component\Foundation\BootstrapManager;

/**
 * @internal
 */
final class BootstrapManagerTest extends MockeryTestCase
{
    public function testBootstrapWith(): void
    {
        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->with('config')
            ->andReturn([]);

        $kernel    = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('setKernelConfigurations')
            ->once()
            ->with([]);

        $boot = new BootstrapManager($kernel);

        $this->assertFalse($boot->hasBeenBootstrapped());

        $boot->bootstrapWith([ConfigureKernel::class]);

        $this->assertTrue($boot->hasBeenBootstrapped());
    }

    public function testAfterAndBeforeBootstrap(): void
    {
        $_SERVER['test'] = 0;

        $container = $this->mock(ContainerContract::class);
        $container->shouldReceive('get')
            ->with('config')
            ->andReturn([]);

        $kernel    = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('setKernelConfigurations')
            ->once()
            ->with([]);

        $boot = new BootstrapManager($kernel);

        $boot->addBeforeBootstrapping(ConfigureKernel::class, static function (): void {
            $_SERVER['test'] = 1;
        });

        $boot->addAfterBootstrapping(ConfigureKernel::class, static function (): void {
            $_SERVER['test'] = 3;
        });

        $boot->bootstrapWith([ConfigureKernel::class]);

        $this->assertTrue($boot->hasBeenBootstrapped());
        $this->assertSame(3, $_SERVER['test']);
    }
}
