<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Container\ContainerInterface;
use Viserio\Bridge\Monolog\Processor\DebugProcessor;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Log\DataCollector\LoggerDataCollector;
use Viserio\Component\Log\LogManager;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class LoggerDataCollectorServiceProvider implements
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
            LogManager::class       => [self::class, 'extendLogManager'],
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
     * @param \Psr\Container\ContainerInterface                      $container
     * @param null|\Monolog\Logger|\Viserio\Component\Log\LogManager $logManager
     *
     * @return null|\Monolog\Logger|\Viserio\Component\Log\Logger
     */
    public static function extendLogManager(ContainerInterface $container, $logManager = null)
    {
        $options = self::resolveOptions($container);

        if ($logManager !== null && $options['collector']['logs'] === true) {
            $logManager->pushProcessor(new DebugProcessor());
        }

        return $logManager;
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

            if ($options['collector']['logs'] === true && $container->has(LogManager::class)) {
                $profiler->addCollector(new LoggerDataCollector($container->get(LogManager::class)->getDriver()));
            }
        }

        return $profiler;
    }
}
