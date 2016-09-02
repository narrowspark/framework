<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Providers;

use Interop\Container\ContainerInterface;
use Interop\Container\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Viserio\Config\Manager as ConfigManager;
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
            'flysystem.cachefactory' => function (ContainerInterface $container) {
                return $container->get('todo');
            },
        ];
    }

    public static function createFilesystemManager(ContainerInterface $container): FilesystemManager
    {
        return new FilesystemManager($container->get(ConfigManager::class));
    }

    public static function createFlysystemConnection(ContainerInterface $container)
    {
        return $container->get(FilesystemManager::class)->connection();
    }

    public static function createCacheFactory()
    {
    }
}
