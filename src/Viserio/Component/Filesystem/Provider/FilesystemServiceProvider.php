<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Provider;

use Interop\Container\ServiceProviderInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Psr\Container\ContainerInterface;
use Viserio\Component\Contract\Cache\Manager as CacheManagerContract;
use Viserio\Component\Filesystem\Cache\CachedFactory;
use Viserio\Component\Filesystem\FilesystemManager;

class FilesystemServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFactories(): array
    {
        return [
            FilesystemManager::class => [self::class, 'createFilesystemManager'],
            'flysystem'              => function (ContainerInterface $container) {
                return $container->get(FilesystemManager::class);
            },
            'flysystem.connection' => [self::class, 'createFlysystemConnection'],
            Filesystem::class      => function (ContainerInterface $container) {
                return $container->get(FilesystemManager::class);
            },
            FilesystemInterface::class => function (ContainerInterface $container) {
                return $container->get(FilesystemManager::class);
            },
            CachedFactory::class      => [self::class, 'createCachedFactory'],
            'flysystem.cachedfactory' => function (ContainerInterface $container) {
                return $container->get(CachedFactory::class);
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
     * Create a new FilesystemManager instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Filesystem\FilesystemManager
     */
    public static function createFilesystemManager(ContainerInterface $container): FilesystemManager
    {
        $manager = new FilesystemManager($container);

        if ($container->has(CacheManagerContract::class)) {
            $manager->setCacheManager($container->get(CacheManagerContract::class));
        }

        return $manager;
    }

    /**
     * Create a new Connector instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Contract\Filesystem\Connector|\Viserio\Component\Filesystem\FilesystemAdapter
     */
    public static function createFlysystemConnection(ContainerInterface $container)
    {
        return $container->get(FilesystemManager::class)->getConnection();
    }

    /**
     * Create a new CachedFactory instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return \Viserio\Component\Filesystem\Cache\CachedFactory
     */
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
