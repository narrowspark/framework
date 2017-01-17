<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Providers;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cache\Providers\CacheServiceProvider;
use Viserio\Component\Config\Providers\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Filesystem\Cache\CachedFactory;
use Viserio\Component\Filesystem\FilesystemAdapter;
use Viserio\Component\Filesystem\FilesystemManager;
use Viserio\Component\Filesystem\Providers\FilesystemServiceProvider;

class FilesystemServiceProviderTest extends TestCase
{
    public function testProvider()
    {
        $container = new Container();
        $container->register(new ConfigServiceProvider());
        $container->register(new FilesystemServiceProvider());
        $container->register(new CacheServiceProvider());

        $container->get('config')->setArray([
            'filesystem.connections' => [
                'local' => [
                    'path' => __DIR__, 'prefix' => 'your-prefix',
                ],
            ],
        ]);

        self::assertInstanceOf(FilesystemManager::class, $container->get(FilesystemManager::class));
        self::assertInstanceOf(FilesystemManager::class, $container->get(Filesystem::class));
        self::assertInstanceOf(FilesystemManager::class, $container->get(FilesystemInterface::class));
        self::assertInstanceOf(FilesystemManager::class, $container->get('flysystem'));
        self::assertInstanceOf(FilesystemAdapter::class, $container->get('flysystem.connection'));
        self::assertInstanceOf(CachedFactory::class, $container->get(CachedFactory::class));
        self::assertInstanceOf(CachedFactory::class, $container->get('flysystem.cachedfactory'));

        self::assertInstanceOf(FilesystemManager::class, $container->get(FilesystemManager::class));
        self::assertInstanceOf(CachedFactory::class, $container->get(CachedFactory::class));
    }
}
