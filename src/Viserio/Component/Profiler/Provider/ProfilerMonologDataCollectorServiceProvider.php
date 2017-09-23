<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Provider;

use Interop\Container\ServiceProviderInterface;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Component\Profiler\DataCollector\Bridge\Log\DebugProcessor;
use Viserio\Component\Profiler\DataCollector\Bridge\Log\MonologLoggerDataCollector;

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
            ProfilerContract::class => [self::class, 'extendProfiler'],
            Logger::class           => [self::class, 'extendLogger'],
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
     * @param null|\Monolog\Logger|\Viserio\Component\Log\Writer $log
     *
     * @return null|\Monolog\Logger|\Viserio\Component\Log\Writer
     */
    public static function extendLogger(ContainerInterface $container, $log = null)
    {
        if ($log !== null) {
            $log->pushProcessor(new DebugProcessor());
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
