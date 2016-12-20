<?php
declare(strict_types=1);
namespace Viserio\Cache\Providers;

use Cache\Adapter\Chain\CachePoolChain;
use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Cache\CacheManager;
use Viserio\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Support\Traits\ServiceProviderConfigAwareTrait;

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
            CachePoolChain::class => [self::class, 'registerChainAdapter'],
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

    public static function registerChainAdapter(ContainerInterface $container): CachePoolChain
    {
        if ($services = self::getConfig($container, 'chains.services', false)) {
            $chains = [];

            foreach ($services as $service) {
                $chains[] = $container->get($service);
            }

            return new CachePoolChain($chains, self::getConfig($container, 'chains.options', []));
        }
    }
}
