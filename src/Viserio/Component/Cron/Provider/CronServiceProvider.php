<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class CronServiceProvider implements
    ServiceProviderInterface,
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
            Schedule::class => [self::class, 'createSchedule'],
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
