<?php
declare(strict_types=1);
namespace Viserio\Cache\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Cache\CacheManager;
use Viserio\Cache\DataCollectors\ViserioCacheDataCollector;
use Viserio\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Contracts\Config\Repository as RepositoryContract;

class CacheServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            ViserioCacheDataCollector::class => [self::class, 'registerViserioCacheDataCollector'],
            CacheManagerContract::class => [self::class, 'registerCacheFactory'],
            CacheManager::class => function (ContainerInterface $container) {
                return $container->get(CacheManagerContract::class);
            },
            'cache' => function (ContainerInterface $container) {
                return $container->get(CacheManagerContract::class);
            },
            CacheItemPoolInterface::class => [self::class, 'registerDefaultCache'],
            'cache.store' => function (ContainerInterface $container) {
                return $container->get(CacheItemPoolInterface::class);
            },
        ];
    }

    public static function registerCacheFactory(ContainerInterface $container): CacheManager
    {
        $cache = new CacheManager($container->get(RepositoryContract::class));
        $cache->setContainer($container);

        return $cache;
    }

    public static function registerDefaultCache(ContainerInterface $container): CacheItemPoolInterface
    {
        return $container->get(CacheManager::class)->driver();
    }

    public static function registerViserioCacheDataCollector(ContainerInterface $container): ViserioCacheDataCollector
    {
        return new ViserioCacheDataCollector($container->get(CacheItemPoolInterface::class));
    }
}
