<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Provider;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cache\Provider\CacheServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Filesystem\Cache\CachedFactory;
use Viserio\Component\Filesystem\FilesystemAdapter;
use Viserio\Component\Filesystem\FilesystemManager;
use Viserio\Component\Filesystem\Provider\FilesystemServiceProvider;

/**
 * @internal
 */
final class FilesystemServiceProviderTest extends TestCase
{
    public function testProvider(): void
    {
        $container = new Container();
        $container->register(new FilesystemServiceProvider());
        $container->register(new CacheServiceProvider());
        $container->instance('config', [
            'viserio' => [
                'filesystem' => [
                    'default'     => 'local',
                    'connections' => [
                        'local' => [
                            'path' => __DIR__, 'prefix' => 'your-prefix',
                        ],
                    ],
                ],
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ],
        ]);

        static::assertInstanceOf(FilesystemManager::class, $container->get(FilesystemManager::class));
        static::assertInstanceOf(FilesystemManager::class, $container->get(Filesystem::class));
        static::assertInstanceOf(FilesystemManager::class, $container->get(FilesystemInterface::class));
        static::assertInstanceOf(FilesystemManager::class, $container->get('flysystem'));
        static::assertInstanceOf(FilesystemAdapter::class, $container->get('flysystem.connection'));
        static::assertInstanceOf(CachedFactory::class, $container->get(CachedFactory::class));
        static::assertInstanceOf(CachedFactory::class, $container->get('flysystem.cachedfactory'));

        static::assertInstanceOf(FilesystemManager::class, $container->get(FilesystemManager::class));
        static::assertInstanceOf(CachedFactory::class, $container->get(CachedFactory::class));
    }
}
