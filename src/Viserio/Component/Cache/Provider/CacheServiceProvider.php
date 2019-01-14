<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Provider;

use Interop\Container\ServiceProviderInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Contract\Cache\Manager as CacheManagerContract;

class CacheServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            CacheManagerContract::class => [self::class, 'createCacheManager'],
            CacheManager::class         => static function (ContainerInterface $container): CacheManagerContract {
                return $container->get(CacheManagerContract::class);
            },
            'cache' => static function (ContainerInterface $container): CacheManagerContract {
                return $container->get(CacheManagerContract::class);
            },
            CacheItemPoolInterface::class => [self::class, 'registerDefaultCache'],
            CacheInterface::class         => [self::class, 'registerDefaultCache'],
            'cache.store'                 => static function (ContainerInterface $container) {
                return $container->get(CacheItemPoolInterface::class);
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getExtensions(): array
    {
        return [];
    }

    /**
     * A instance of the default driver.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Psr\Cache\CacheItemPoolInterface|\Psr\SimpleCache\CacheInterface
     */
    public static function registerDefaultCache(ContainerInterface $container)
    {
        return $container->get(CacheManager::class)->getDriver();
    }

    /**
     * Create a cache manger instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Cache\Manager
     */
    public static function createCacheManager(ContainerInterface $container): CacheManagerContract
    {
        $cache = new CacheManager($container->get('config'));
        $cache->setContainer($container);

        return $cache;
    }
}
