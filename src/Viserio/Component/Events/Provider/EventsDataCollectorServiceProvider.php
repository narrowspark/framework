<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Provider;

use Interop\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Contracts\Events\EventManager as EventManagerContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Events\DataCollector\TraceableEventManager;
use Viserio\Component\Events\DataCollector\ViserioEventsDataCollector;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class EventsDataCollectorServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            EventManagerContract::class => [self::class, 'extendEventManager'],
            ProfilerContract::class     => [self::class, 'extendProfiler'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'profiler'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'collector' => [
                'events' => false,
            ],
        ];
    }

    /**
     * Extend viserio events manager with a new event.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Viserio\Component\Events\DataCollector\TraceableEventManager
     */
    public static function extendEventManager(ContainerInterface $container, ?callable $getPrevious = null): ?TraceableEventManager
    {
        $eventManager = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($eventManager !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['events']) {
                $eventManager = new TraceableEventManager($eventManager, $container->get(Stopwatch::class));

                if ($container->has(LoggerInterface::class)) {
                    $eventManager->setLogger($container->get(LoggerInterface::class));
                }
            }
        }

        return $eventManager;
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
        $profiler = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($profiler !== null) {
            $options = self::resolveOptions($container);

            if ($options['collector']['events']) {
                // @var ProfilerContract $profiler
                $profiler->addCollector(new ViserioEventsDataCollector(
                    $container->get(EventManagerContract::class)
                ));
            }
        }

        return $profiler;
    }
}
