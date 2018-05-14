<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Provider;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Cron\Schedule as ScheduleContract;
use Viserio\Component\Contract\Container\ServiceProvider as ServiceProviderContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class CronServiceProvider implements
    ServiceProviderContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            ScheduleContract::class => [self::class, 'createSchedule'],
            Schedule::class         => function (ContainerInterface $container) {
                return $container->get(ScheduleContract::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return ['path'];
    }

    /**
     * Create a new Schedule instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Cron\Schedule
     */
    public static function createSchedule(ContainerInterface $container): Schedule
    {
        $options = self::resolveOptions($container->get('config'));

        $scheduler = new Schedule($options['path'], $options['console']);

        if ($container->has(CacheItemPoolInterface::class)) {
            $scheduler->setCacheItemPool($container->get(CacheItemPoolInterface::class));
        }

        $scheduler->setContainer($container);

        return $scheduler;
    }
}
