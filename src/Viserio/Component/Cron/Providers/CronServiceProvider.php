<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Contracts\Console\Application as ApplicationContract;
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
            Schedule::class            => [self::class, 'createSchedule'],
            ApplicationContract::class => [self::class, 'createConsoleCommands'],
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

    public static function createConsoleCommands(ContainerInterface $container, ?callable $getPrevious = null): ?ApplicationContract
    {
        if ($getPrevious !== null) {
            $console = $getPrevious();

            $console->addCommands([
                new CronListCommand(),
                new ForgetCommand($container->get(CacheItemPoolInterface::class)),
                new ScheduleRunCommand(),
            ]);

            return $console;
        }

        return null;
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
