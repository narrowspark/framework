<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Provider;

use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class CronServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            Schedule::class => [self::class, 'createSchedule'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
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
        $options = self::resolveOptions($container);

        $scheduler = new Schedule(
            $options['path'],
            $options['console']
        );

        if ($container->has(CacheItemPoolInterface::class)) {
            $scheduler->setCacheItemPool($container->get(CacheItemPoolInterface::class));
        }

        $scheduler->setContainer($container);

        return $scheduler;
    }
}
