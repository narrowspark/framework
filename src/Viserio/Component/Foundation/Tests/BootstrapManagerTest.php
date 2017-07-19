<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;
use Viserio\Component\Foundation\BootstrapManager;

class BootstrapManagerTest extends MockeryTestCase
{
    public function testBootstrapWith(): void
    {
        $container = $this->mock(ContainerContract::class);
        $kernel    = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('setKernelConfigurations')
            ->once()
            ->with($container);

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($kernel);

        $container->shouldReceive('resolve')
            ->once()
            ->with(ConfigureKernel::class)
            ->andReturn(new ConfigureKernel());

        $boot = new BootstrapManager($container);

        self::assertFalse($boot->hasBeenBootstrapped());

        $boot->bootstrapWith([ConfigureKernel::class]);

        self::assertTrue($boot->hasBeenBootstrapped());
    }

    public function testAfterAndBeforeBootstrap(): void
    {
        $_SERVER['test'] = 0;

        $container = $this->mock(ContainerContract::class);
        $kernel    = $this->mock(KernelContract::class);
        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);
        $kernel->shouldReceive('setKernelConfigurations')
            ->once()
            ->with($container);

        $container->shouldReceive('get')
            ->once()
            ->with(KernelContract::class)
            ->andReturn($kernel);

        $container->shouldReceive('resolve')
            ->once()
            ->with(ConfigureKernel::class)
            ->andReturn(new ConfigureKernel());

        $boot = new BootstrapManager($container);

        $boot->addBeforeBootstrapping(ConfigureKernel::class, function (): void {
            $_SERVER['test'] = 1;
        });

        $boot->addAfterBootstrapping(ConfigureKernel::class, function (): void {
            $_SERVER['test'] = 3;
        });

        $boot->bootstrapWith([ConfigureKernel::class]);

        self::assertTrue($boot->hasBeenBootstrapped());
        self::assertSame(3, $_SERVER['test']);
    }
}
