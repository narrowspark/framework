<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Routing\Router as RouterContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\Foundation\DataCollectors\FilesLoadedCollector;
use Viserio\Component\Foundation\DataCollectors\NarrowsparkDataCollector;
use Viserio\Component\Foundation\DataCollectors\ViserioHttpDataCollector;

class FoundationDataCollectorsServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            WebProfilerContract::class => [self::class, 'createWebProfiler'],
        ];
    }

    public static function createWebProfiler(ContainerInterface $container): WebProfilerContract
    {
        $profiler = $container->get(WebProfilerContract::class);

        if (self::getConfig($container, 'collector.narrowspark', false)) {
            $profiler->addCollector(static::createNarrowsparkDataCollector());
        }

        if (self::getConfig($container, 'collector.viserio.http', false)) {
            $profiler->addCollector(static::createViserioHttpDataCollector($container), 1);
        }

        if (self::getConfig($container, 'collector.files', false)) {
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
}
