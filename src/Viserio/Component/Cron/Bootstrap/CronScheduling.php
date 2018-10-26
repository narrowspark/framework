<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Bootstrap;

use Closure;
use Viserio\Component\Contract\Cron\CronJob as CronJobContract;
use Viserio\Component\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\AbstractLoadFiles;
use Viserio\Component\Foundation\Bootstrap\LoadServiceProvider;

class CronScheduling extends AbstractLoadFiles implements BootstrapStateContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 128;
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
        return LoadServiceProvider::class;
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
