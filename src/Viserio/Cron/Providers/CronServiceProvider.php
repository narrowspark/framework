<?php
declare(strict_types=1);
namespace Viserio\Cron\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Cron\Commands\CronListCommand;
use Viserio\Cron\Commands\ForgetCommand;
use Viserio\Cron\Commands\ScheduleRunCommand;
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
            'cron.commands' => [self::class, 'createCronCommands'],
        ];
    }

    public static function createSchedule(ContainerInterface $container): Schedule
    {
        $scheduler = new Schedule(
            $container->get(CacheItemPoolInterface::class),
            self::getConfig($container, 'path'),
            self::getConfig($container, 'console')
        );

        $scheduler->setContainer($container);

        return $scheduler;
    }

    public static function createCronCommands(): array
    {
        return [
            new CronListCommand(),
            new ForgetCommand(),
            new ScheduleRunCommand(),
        ];
    }
}
