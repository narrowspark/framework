<?php
declare(strict_types=1);
namespace Viserio\Foundation\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Routing\Router as RouterContract;
use Viserio\Foundation\DataCollectors\FilesLoadedCollector;
use Viserio\Foundation\DataCollectors\NarrowsparkDataCollector;
use Viserio\Foundation\DataCollectors\ViserioHttpDataCollector;

class FoundationDataCollectorsServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            NarrowsparkDataCollector::class => [self::class, 'createNarrowsparkDataCollector'],
            ViserioHttpDataCollector::class => [self::class, 'createViserioHttpDataCollector'],
            FilesLoadedCollector::class => [self::class, 'createFilesLoadedCollector'],
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

    public static function createFilesLoadedCollector(ContainerInterface $container): FilesLoadedCollector
    {
        $config = $container->get(RepositoryContract::class);

        return new FilesLoadedCollector($config->get('path.base'));
    }
}
