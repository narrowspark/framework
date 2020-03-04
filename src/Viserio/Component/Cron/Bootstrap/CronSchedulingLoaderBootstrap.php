<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Cron\Bootstrap;

use Closure;
use Viserio\Contract\Cron\CronJob as CronJobContract;
use Viserio\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Contract\Foundation\Kernel as KernelContract;
use Viserio\Provider\Framework\Bootstrap\InitializeContainerBootstrap;

class CronSchedulingLoaderBootstrap implements BootstrapStateContract
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
