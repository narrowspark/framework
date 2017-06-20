<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Events\DataCollector\ViserioEventsDataCollector;
use Viserio\Component\OptionsResolver\Traits\StaticOptionsResolverTrait;

class EventsDataCollectorServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use StaticOptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ProfilerContract::class => [self::class, 'extendProfiler'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'profiler'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'collector' => [
                'events' => false,
            ],
        ];
    }

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\Profiler\Profiler
     */
    public static function extendProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?ProfilerContract
    {
        $profiler = $getPrevious();

        if ($profiler !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['events']) {
                $collector = new ViserioEventsDataCollector(
                    $container->get(ServerRequestInterface::class)
                );

                $events = $container->get(EventManagerContract::class);
                $events->attach('#', [$collector, 'subscribe']);

                $profiler->addCollector($collector);
            }
        }

        return $profiler;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigClass(): RequiresConfigContract
    {
        return new self();
    }
}
