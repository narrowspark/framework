<?php
declare(strict_types=1);
namespace Viserio\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Foundation\DataCollectors\NarrowsparkDataCollector;
use Viserio\Foundation\DataCollectors\ViserioRequestDataCollector;
use Viserio\Foundation\DataCollectors\ViserioViewDataCollector;
use Viserio\WebProfiler\DataCollectors\ViserioConfigDataCollector;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

class FoundationDataCollectorsServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    const PACKAGE = 'viserio.webprofiler';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            NarrowsparkDataCollector::class => [self::class, 'createNarrowsparkDataCollector'],
            ViserioRequestDataCollector::class => [self::class, 'createViserioRequestDataCollector'],
            ViserioConfigDataCollector::class => [self::class, 'createViserioConfigDataCollector'],
            ViserioViewDataCollector::class => [self::class, 'createViserioViewDataCollector'],
        ];
    }

    public static function createNarrowsparkDataCollector(): NarrowsparkDataCollector
    {
        return new NarrowsparkDataCollector();
    }

    public static function createViserioRequestDataCollector(ContainerInterface $container): ViserioRequestDataCollector
    {
        return new ViserioRequestDataCollector(
            $container->get(RouterContract::class),
            $container->get(RepositoryContract::class)
        );
    }

    public static function createViserioViewDataCollector(ContainerInterface $container): ViserioViewDataCollector
    {
        return new ViserioViewDataCollector(
            self::getConfig($container, 'collector.view.collect_data', true)
        );
    }

    public static function createViserioConfigDataCollector(ContainerInterface $container)
    {
        if (!$container->has(RepositoryContract::class)) {
            return;
        }

        return new ViserioConfigDataCollector(
            $container->get(RepositoryContract::class)->getAll()
        );
    }
}
