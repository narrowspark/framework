<?php
declare(strict_types=1);
namespace Viserio\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;
use Viserio\Foundation\DataCollectors\NarrowsparkDataCollector;
use Viserio\Foundation\DataCollectors\ViserioHttpDataCollector;

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
            ViserioHttpDataCollector::class => [self::class, 'createViserioHttpDataCollector'],
        ];
    }

    public static function createNarrowsparkDataCollector(): NarrowsparkDataCollector
    {
        return new NarrowsparkDataCollector();
    }

    public static function createViserioHttpDataCollector(ContainerInterface $container): ViserioHttpDataCollector
    {
        return new ViserioHttpDataCollector(
            $container->get(RouterContract::class),
            $container->get(RepositoryContract::class)
        );
    }
}
