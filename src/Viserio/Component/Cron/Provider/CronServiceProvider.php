<?php
declare(strict_types=1);
namespace Viserio\Component\Cron\Provider;

use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Cron\Schedule;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class CronServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionsContract
{
    use StaticOptionsResolverTrait;

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
    public function getDimensions(): iterable
    {
        return ['viserio', 'cron'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['path'];
    }

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

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
