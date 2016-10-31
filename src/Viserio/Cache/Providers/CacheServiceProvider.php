<?php
declare(strict_types=1);
namespace Viserio\Cache\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Cache\CacheManager;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Cache\Manager as CacheManagerContract;

class CacheServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheManagerContract::class => [self::class, 'registerCacheFactory'],
            CacheManager::class => function (ContainerInterface $container) {
                return $container->get(CacheManagerContract::class);
            },
            'cache' => function (ContainerInterface $container) {
                return $container->get(CacheManagerContract::class);
            },
            CacheItemPoolInterface::class => [self::class, 'registerDefaultCache'],
            'cache.store' => function (ContainerInterface $container) {
                return $container->get('cache.store');
            },
        ];
    }

    public static function registerCacheFactory(ContainerInterface $container): CacheManager
    {
        $cache = new CacheManager($container->get(ConfigManager::class));
        $cache->setContainer($container);

        return $cache;
    }

    public static function registerDefaultCache(ContainerInterface $container): CacheItemPoolInterface
    {
        return $container->get(CacheManager::class)->driver();
    }
}
