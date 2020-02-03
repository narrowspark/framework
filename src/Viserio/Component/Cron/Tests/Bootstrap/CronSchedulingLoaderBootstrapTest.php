<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Cron\Tests\Bootstrap;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Cron\Bootstrap\CronSchedulingLoaderBootstrap;
use Viserio\Contract\Container\CompiledContainer as CompiledContainerContract;
use Viserio\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Provider\Framework\Bootstrap\InitializeContainerBootstrap;

/**
 * @internal
 *
 * @small
 */
final class CronSchedulingLoaderBootstrapTest extends MockeryTestCase
{
    public function testGetPriority(): void
    {
        self::assertSame(256, CronSchedulingLoaderBootstrap::getPriority());
    }

    public function testGetType(): void
    {
        self::assertSame(BootstrapStateContract::TYPE_AFTER, CronSchedulingLoaderBootstrap::getType());
    }

    public function testGetBootstrapper(): void
    {
        self::assertSame(InitializeContainerBootstrap::class, CronSchedulingLoaderBootstrap::getBootstrapper());
    }

    public function testBootstrap(): void
    {
        $containerMock = Mockery::mock(CompiledContainerContract::class);
        $containerMock->shouldReceive('get')
            ->once()
            ->with(ScheduleContract::class)
            ->andReturn(Mockery::mock(ScheduleContract::class));

        $kernel = $this->arrangeKernel($containerMock);
        $kernel->shouldReceive('getConfigPath')
            ->once()
            ->with('cron.php')
            ->andReturn(\dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'cron_jobs.php');

        $bootstrap = new CronSchedulingLoaderBootstrap();

        $bootstrap->bootstrap($kernel);
    }

    /**
     * @param \Mockery\MockInterface|\Viserio\Contract\Container\Container $container
     *
     * @return \Mockery\MockInterface|\Viserio\Contract\Foundation\Kernel
     */
    private function arrangeKernel($container)
    {
        $kernel = Mockery::mock(KernelContract::class);

        $kernel->shouldReceive('getContainer')
            ->once()
            ->andReturn($container);

        return $kernel;
    }
}
