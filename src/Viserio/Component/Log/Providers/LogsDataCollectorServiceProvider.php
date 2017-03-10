<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\Log\DataCollectors\LogParser;
use Viserio\Component\Log\DataCollectors\LogsDataCollector;
use Viserio\Component\OptionsResolver\OptionsResolver;

class LogsDataCollectorServiceProvider implements
    ServiceProvider,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract
{
    /**
     * Resolved cached options.
     *
     * @var array
     */
    private static $options;

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            LogParser::class           => [self::class, 'createLogParser'],
            WebProfilerContract::class => [self::class, 'createWebProfiler'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'webprofiler'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return ['logs_storages'];
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

    public static function createLogParser(ContainerInterface $container): LogParser
    {
        return new LogParser();
    }

    public static function createWebProfiler(ContainerInterface $container, ?callable $getPrevious = null): ?WebProfilerContract
    {
        if ($getPrevious !== null) {
            self::resolveOptions($container);

            $profiler = $getPrevious();

            if (self::$options['collector']['logs']) {
                $profiler->addCollector(new LogsDataCollector(
                    $container->get(LogParser::class),
                    self::$options['logs_storages']
                ));
            }

            return $profiler;
        }

        return null;
    }

    /**
     * Resolve component options.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return void
     */
    private static function resolveOptions(ContainerInterface $container): void
    {
        if (self::$options === null) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
