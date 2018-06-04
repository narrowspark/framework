<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Provider;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Cache\Provider\CacheServiceProvider;
use Viserio\Component\Config\Provider\ConfigServiceProvider;
use Viserio\Component\Container\Container;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
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
        $container->register(new ConfigServiceProvider());
        $container->register(new FilesystemServiceProvider());
        $container->register(new CacheServiceProvider());
        $container->get(RepositoryContract::class)->setArray([
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

        $this->assertInstanceOf(FilesystemManager::class, $container->get(FilesystemManager::class));
        $this->assertInstanceOf(FilesystemManager::class, $container->get(Filesystem::class));
        $this->assertInstanceOf(FilesystemManager::class, $container->get(FilesystemInterface::class));
        $this->assertInstanceOf(FilesystemManager::class, $container->get('flysystem'));
        $this->assertInstanceOf(FilesystemAdapter::class, $container->get('flysystem.connection'));
        $this->assertInstanceOf(CachedFactory::class, $container->get(CachedFactory::class));
        $this->assertInstanceOf(CachedFactory::class, $container->get('flysystem.cachedfactory'));

        $this->assertInstanceOf(FilesystemManager::class, $container->get(FilesystemManager::class));
        $this->assertInstanceOf(CachedFactory::class, $container->get(CachedFactory::class));
    }
}
