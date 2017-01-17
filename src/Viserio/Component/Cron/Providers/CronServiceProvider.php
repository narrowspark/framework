<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Cron\Commands\CronListCommand;
use Viserio\Component\Cron\Commands\ForgetCommand;
use Viserio\Component\Cron\Commands\ScheduleRunCommand;
use Viserio\Component\Cron\Schedule;

class CronServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.cron';

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

    public static function createCronCommands(ContainerInterface $container): array
    {
        return [
            new CronListCommand(),
            new ForgetCommand($container->get(CacheItemPoolInterface::class)),
            new ScheduleRunCommand(),
        ];
    }
}
