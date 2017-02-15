<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\Foundation\DataCollectors\FilesLoadedCollector;
use Viserio\Component\Foundation\DataCollectors\NarrowsparkDataCollector;
use Viserio\Component\Foundation\DataCollectors\ViserioHttpDataCollector;
use Viserio\Component\OptionsResolver\OptionsResolver;

class FoundationDataCollectorsServiceProvider implements
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
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'collector' => [
                'narrowspark' => false,
                'files'       => false,
                'viserio'     => [
                    'http' => false,
                ],
            ],
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        self::resolveOptions($container);

        $profiler = $container->get(WebProfilerContract::class);

        if (self::$options['collector']['narrowspark']) {
            $profiler->addCollector(static::createNarrowsparkDataCollector(), -100);
        }

        if (self::$options['collector']['viserio']['http']) {
            $profiler->addCollector(static::createViserioHttpDataCollector($container), 1);
        }

        if (self::$options['collector']['files']) {
            $profiler->addCollector(self::createFilesLoadedCollector($container));
        }

        return $profiler;
    }

    private static function createNarrowsparkDataCollector(): NarrowsparkDataCollector
    {
        return new NarrowsparkDataCollector();
    }

    private static function createViserioHttpDataCollector(ContainerInterface $container): ViserioHttpDataCollector
    {
        return new ViserioHttpDataCollector(
            $container->get(RouterContract::class),
            $container->get(RepositoryContract::class)
        );
    }

    private static function createFilesLoadedCollector(ContainerInterface $container): FilesLoadedCollector
    {
        $config = $container->get(RepositoryContract::class);

        return new FilesLoadedCollector($config->get('path.base'));
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
