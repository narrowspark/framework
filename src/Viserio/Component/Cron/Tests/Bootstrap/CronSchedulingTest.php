<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Container\Container as ContainerContract;
use Viserio\Component\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Cron\Bootstrap\CronScheduling;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;

/**
 * @internal
 */
final class CronSchedulingTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        $this->assertSame(128, CronScheduling::getPriority());
    }

    public function testGetType(): void
    {
        $this->assertSame(BootstrapStateContract::TYPE_AFTER, CronScheduling::getType());
    }

    public function testGetBootstrapper(): void
    {
        $this->assertSame(LoadServiceProvider::class, CronScheduling::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $containerMock = $this->mock(ContainerContract::class);
        $containerMock->shouldReceive('get')
            ->once()
            ->with(ScheduleContract::class)
            ->andReturn($this->mock(ScheduleContract::class));

        $kernel = $this->arrangeKernel($containerMock);
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('cron.php')
            ->andReturn(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'cron_jobs.php');

        $bootstrap = new CronScheduling();

        $bootstrap->bootstrap($kernel);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Component\Contract\Container\Container $container
     *
     * @return \Mockery\MockInterface|\Viserio\Component\Contract\Foundation\Kernel
     */
    private function arrangeKernel($container)
    {
        $kernel = $this->mock(KernelContract::class);

        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        return $kernel;
    }
}
