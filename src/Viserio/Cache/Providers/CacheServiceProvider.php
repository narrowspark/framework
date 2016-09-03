<?php
declare(strict_types=1);
namespace Viserio\Cache\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Cache\CacheManager;
use Viserio\Config\Manager as ConfigManager;

class CacheServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheManager::class => [self::class, 'registerCacheFactory'],
            'cache' => function (ContainerInterface $container) {
                return $container->get(CacheManager::class);
            },
            'cache.store' => [self::class, 'registerDefaultCache'],
        ];
    }

    public static function registerCacheFactory(ContainerInterface $container)
    {
        $cache = new CacheManager($container->get(ConfigManager::class));
        $cache->setContainer($container);

        return $cache;
    }

    public static function registerDefaultCache(ContainerInterface $container)
    {
        return $container->get(CacheManager::class)->driver();
    }
}
