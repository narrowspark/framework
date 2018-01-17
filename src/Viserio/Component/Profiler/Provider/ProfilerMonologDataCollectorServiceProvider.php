<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Interop\Container\ServiceProviderInterface;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Log\Logger;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\Profiler\DataCollector\Bridge\Monolog\DebugProcessor;
use Viserio\Component\Profiler\DataCollector\Bridge\Monolog\MonologLoggerDataCollector;

class ProfilerMonologDataCollectorServiceProvider implements
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
    public function getExtensions(): array
    {
        return [
            Logger::class           => [self::class, 'extendLogger'],
            ProfilerContract::class => [self::class, 'extendProfiler'],
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
                'logs' => false,
            ],
        ];
    }

    /**
     * Extend monolog with a processor.
     *
     * @param \Psr\Container\ContainerInterface                  $container
     * @param null|\Monolog\Logger|\Viserio\Component\Log\Logger $log
     *
     * @return null|\Monolog\Logger|\Viserio\Component\Log\Logger
     */
    public static function extendLogger(ContainerInterface $container, $log = null)
    {
        $options = self::resolveOptions($container);

        if ($log !== null && $options['collector']['logs'] === true) {
            if ($log instanceof Logger) {
                $log->getMonolog()->pushProcessor(new DebugProcessor());
            } else {
                $log->pushProcessor(new DebugProcessor());
            }
        }

        return $log;
    }

    /**
     * Extend viserio profiler with a data collector.
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
            $options = self::resolveOptions($container);

            if ($options['collector']['logs'] === true && $container->has(Logger::class)) {
                $profiler->addCollector(new MonologLoggerDataCollector($container->get(Logger::class)));
            }
        }

        return $profiler;
    }
}
