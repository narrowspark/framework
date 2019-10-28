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

namespace Viserio\Component\Cron\Bootstrap;

use Closure;
use Viserio\Component\Container\Bootstrap\InitializeContainerBootstrap;
use Viserio\Component\Foundation\Bootstrap\AbstractFilesLoaderBootstrap;
use Viserio\Contract\Cron\CronJob as CronJobContract;
use Viserio\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;

class CronSchedulingLoaderBootstrap extends AbstractFilesLoaderBootstrap implements BootstrapStateContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 256;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return BootstrapStateContract::TYPE_AFTER;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBootstrapper(): string
    {
        return InitializeContainerBootstrap::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function isSupported(KernelContract $kernel): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $schedule = $kernel->getContainer()->get(ScheduleContract::class);

        foreach ((array) require $kernel->getConfigPath('cron.php') as $class) {
            if ($class instanceof CronJobContract) {
                $class::register($schedule);
            } elseif ($class instanceof Closure) {
                $class($schedule);
            }
        }
    }
}
