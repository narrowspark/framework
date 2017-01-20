<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Component\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

class CacheServiceProvider implements ServiceProvider
{
    use ServiceProviderConfigAwareTrait;

    public const PACKAGE = 'viserio.cache';

    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            CacheManagerContract::class => [self::class, 'registerCacheFactory'],
            CacheManager::class         => function (ContainerInterface $container) {
                return $container->get(CacheManagerContract::class);
            },
            'cache' => function (ContainerInterface $container) {
                return $container->get(CacheManagerContract::class);
            },
            CacheItemPoolInterface::class => [self::class, 'registerDefaultCache'],
            'cache.store'                 => function (ContainerInterface $container) {
                return $container->get(CacheItemPoolInterface::class);
            },
        ];
    }

    public static function registerCacheFactory(ContainerInterface $container): CacheManager
    {
        return new CacheManager($container);
    }

    public static function registerDefaultCache(ContainerInterface $container): CacheItemPoolInterface
    {
        return $container->get(CacheManager::class)->getDriver();
    }
}
