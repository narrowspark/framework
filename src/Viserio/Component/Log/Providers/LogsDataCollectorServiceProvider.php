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
    private static $options = [];

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

    /**
     * Create a handler parser.
     *
     * @return \Viserio\Component\Log\DataCollectors\LogParser
     */
    public static function createLogParser(): LogParser
    {
        return new LogParser();
    }

    /**
     * Extend viserio profiler with data collector.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param null|callable                         $getPrevious
     *
     * @return null|\Viserio\Component\Contracts\WebProfiler\WebProfiler
     */
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
        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
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
        if (count(self::$options) === 0) {
            self::$options = $container->get(OptionsResolver::class)
                ->configure(new static(), $container)
                ->resolve();
        }
    }
}
