<?php
declare(strict_types=1);
namespace Viserio\Cron\Providers;

use Viserio\Cron\Schedule;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

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
            Schedule::class => [self::class, 'createSchedule']
        ];
    }

    public static function createSchedule(ContainerInterface $container)
    {
        $scheduler = new Schedule;

        $scheduler->setContainer($container);
        $scheduler->setConsoleName(self::getConfig($container, 'console'));

        return $scheduler;
    }
}
