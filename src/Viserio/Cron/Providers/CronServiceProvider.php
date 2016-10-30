<?php
declare(strict_types=1);
namespace Viserio\Cron\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Cron\Schedule;

class CronServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.cron';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Schedule::class => [self::class, 'createSchedule'],
        ];
    }

    public static function createSchedule(ContainerInterface $container)
    {
        $scheduler = new Schedule(
            self::getConfig($container, 'path'),
            self::getConfig($container, 'mutex_path'),
            self::getConfig($container, 'console')
        );

        $scheduler->setContainer($container);

        return $scheduler;
    }
}
