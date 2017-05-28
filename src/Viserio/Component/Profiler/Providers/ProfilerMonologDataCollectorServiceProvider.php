<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Providers;

use Interop\Container\ServiceProvider;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Viserio\Component\OptionsResolver\OptionsResolver;
use Viserio\Component\Profiler\DataCollectors\Bridge\Log\DebugProcessor;
use Viserio\Component\Profiler\DataCollectors\Bridge\Log\MonologLoggerDataCollector;

class ProfilerMonologDataCollectorServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options = [];

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ProfilerContract::class => [self::class, 'extendProfiler'],
            Logger::class           => [self::class, 'extendLogger'],
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
                'logs' => false,
            ],
        ];
    }

    /**
     * Extend monolog with a processor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param null|callable                     $getPrevious
     *
     * @return null|\Monolog\Logger|\Viserio\Component\Log\Writer
     */
    public static function extendLogger(ContainerInterface $container, ?callable $getPrevious = null)
    {
        $log = is_callable($getPrevious) ? $getPrevious() : $getPrevious;

        if ($log !== null) {
            $log->pushProcessor(new DebugProcessor());
        }

        return $log;
    }

    /**
     * Extend viserio profiler with a data collector.
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
            self::resolveOptions($container);

            if (self::$options['collector']['logs'] === true && $container->has(Logger::class)) {
                $profiler->addCollector(new MonologLoggerDataCollector($container->get(Logger::class)));
            }

            return $profiler;
        }

        return $profiler;
    }

    /**
     * Resolve component options.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (count(self::$options) === 0) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
