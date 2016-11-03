<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Viserio\Config\Manager as ConfigManager;
use Viserio\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Filesystem\Cache\CachedFactory;
use Viserio\Filesystem\FilesystemManager;

class FilesystemServiceProvider implements ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function getServices()
    {
        return [
            FilesystemManager::class => [self::class, 'createFilesystemManager'],
            'flysystem' => function (ContainerInterface $container) {
                return $container->get(FilesystemManager::class);
            },
            'flysystem.connection' => [self::class, 'createFlysystemConnection'],
            Filesystem::class => function (ContainerInterface $container) {
                return $container->get(FilesystemManager::class);
            },
            FilesystemInterface::class => function (ContainerInterface $container) {
                return $container->get(FilesystemManager::class);
            },
            CachedFactory::class => [self::class, 'createCachedFactory'],
            'flysystem.cachedfactory' => function (ContainerInterface $container) {
                return $container->get(CachedFactory::class);
            },
        ];
    }

    public static function createFilesystemManager(ContainerInterface $container): FilesystemManager
    {
        $manager = new FilesystemManager($container->get(ConfigManager::class));

        if ($container->has(CacheManagerContract::class)) {
            $manager->setCacheManager($container->get(CacheManagerContract::class));
        }

        return $manager;
    }

    public static function createFlysystemConnection(ContainerInterface $container)
    {
        return $container->get(FilesystemManager::class)->connection();
    }

    public static function createCachedFactory(ContainerInterface $container): CachedFactory
    {
        $cache = null;

        if ($container->has(CacheManagerContract::class)) {
            $cache = $container->get(CacheManagerContract::class);
        }

        return new CachedFactory(
            $container->get(FilesystemManager::class),
            $cache
        );
    }
}
