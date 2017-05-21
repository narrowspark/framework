<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;

class CacheServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheManagerContract::class   => function (ContainerInterface $container): CacheManagerContract {
                return new CacheManager($container);
            },
            CacheManager::class           => function (ContainerInterface $container): CacheManagerContract {
                return $container->get(CacheManagerContract::class);
            },
            'cache' => function (ContainerInterface $container): CacheManagerContract {
                return $container->get(CacheManagerContract::class);
            },
            CacheItemPoolInterface::class => [self::class, 'registerDefaultCache'],
            CacheInterface::class         => [self::class, 'registerDefaultCache'],
            'cache.store'                 => function (ContainerInterface $container) {
                return $container->get(CacheItemPoolInterface::class);
            },
        ];
    }

    /**
     * A instance of the default driver.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return \Psr\Cache\CacheItemPoolInterface|\Psr\SimpleCache\CacheInterface
     */
    public static function registerDefaultCache(ContainerInterface $container)
    {
        return $container->get(CacheManager::class)->getDriver();
    }
}
