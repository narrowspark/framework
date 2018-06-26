<?php
declare(strict_types=1);
namespace Viserio\Component\Events\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Events\DataCollector\TraceableEventManager;
use Viserio\Component\Events\DataCollector\ViserioEventsDataCollector;
use Viserio\Component\Events\EventManager;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class EventsDataCollectorServiceProvider implements
    ServiceProviderInterface,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    use OptionsResolverTrait;

    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions()
    {
        return [
            EventManager::class     => [self::class, 'extendEventManager'],
            ProfilerContract::class => [self::class, 'extendProfiler'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'profiler'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
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
     * @param \Psr\Container\ContainerInterface           $container
     * @param null|\Viserio\Component\Events\EventManager $eventManager
     *
     * @return null|\Viserio\Component\Contract\Events\EventManager|\Viserio\Component\Events\DataCollector\TraceableEventManager
     */
    public static function extendEventManager(
        ContainerInterface $container,
        ?EventManager $eventManager = null
    ): ?TraceableEventManager {
        if ($eventManager !== null) {
            $options = self::resolveOptions($container->get('config'));

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
     * @param \Psr\Container\ContainerInterface                  $container
     * @param null|\Viserio\Component\Contract\Profiler\Profiler $profiler
     *
     * @return null|\Viserio\Component\Contract\Profiler\Profiler
     */
    public static function extendProfiler(
        ContainerInterface $container,
        ?ProfilerContract $profiler = null
    ): ?ProfilerContract {
        if ($profiler !== null) {
            $options = self::resolveOptions($container->get('config'));

            if ($options['collector']['events']) {
                $profiler->addCollector(new ViserioEventsDataCollector(
                    $container->get(EventManager::class)
                ));
            }
        }

        return $profiler;
    }
}
