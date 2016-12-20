<?php
declare(strict_types=1);
namespace Viserio\Cache\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Cache\DataCollectors\ViserioCacheDataCollector;

class CacheCollectorServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ViserioCacheDataCollector::class => [self::class, 'registerViserioCacheDataCollector'],
        ];
    }

    public static function registerViserioCacheDataCollector(ContainerInterface $container): ViserioCacheDataCollector
    {
        return new ViserioCacheDataCollector($container->get(CacheItemPoolInterface::class));
    }
}
