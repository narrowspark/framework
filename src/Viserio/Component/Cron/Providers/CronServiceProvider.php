<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cron\Commands\CronListCommand;
use Viserio\Component\Cron\Commands\ForgetCommand;
use Viserio\Component\Cron\Commands\ScheduleRunCommand;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\OptionsResolver;

class CronServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options;

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

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['path', 'console'];
    }

    public static function createSchedule(ContainerInterface $container): Schedule
    {
        self::resolveOptions($container);

        $scheduler = new Schedule(
            $container->get(CacheItemPoolInterface::class),
            self::$options['path'],
            self::$options['console']
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

    /**
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
