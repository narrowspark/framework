<?php
declare(strict_types=1);
namespace Viserio\Cache\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Cache\CacheManager;

class CacheServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheManager::class => [self::class, 'registerCacheFactory'],
            'cache' => function(ContainerInterface $container) {
                return $container->get(CacheManager::class);
            },
            'cache.store' => [self::class, 'registerDefaultCache'],
        ];
    }

    public static function registerCacheFactory(ContainerInterface $container)
    {
        return new CacheManager($container->get(ConfigManager::class));
    }

    public static function registerDefaultCache(ContainerInterface $container)
    {
        return $container->get(CacheManager::class)->driver();
    }
}
