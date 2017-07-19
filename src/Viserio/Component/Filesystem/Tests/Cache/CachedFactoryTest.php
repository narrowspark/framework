<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use League\Flysystem\Adapter\Local as FlyLocal;
use League\Flysystem\Cached\CacheInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Component\Filesystem\Cache\CachedFactory;
use Viserio\Component\Filesystem\FilesystemManager;

class CachedFactoryTest extends MockeryTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A driver must be specified.
     */
    public function testConnectionThrowsInvalidArgumentException(): void
    {
        $cache = new CachedFactory($this->mock(FilesystemManager::class));

        $cache->getConnection(['test']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported driver [local].
     */
    public function testCreateConnectorThrowsInvalidArgumentException(): void
    {
        $manager = $this->mock(FilesystemManager::class);
        $manager->shouldReceive('hasConnection')
            ->andReturn(false);
        $cache = new CachedFactory($manager);

        $cache->getConnection([
            'cache' => [
                'driver' => 'local',
            ],
        ]);
    }

    public function testConnectionWithFilesystemManager(): void
    {
        $manager = $this->mock(FilesystemManager::class);
        $manager->shouldReceive('hasConnection')
            ->once()
            ->with('local')
            ->andReturn(true);
        $manager->shouldReceive('createConnection')
            ->once()
            ->andReturn($this->mock(FlyLocal::class));

        $cache = new CachedFactory($manager);

        $adapter = $cache->getConnection([
            'cache' => [
                'driver' => 'local',
                'name'   => 'local',
                'key'    => 'test',
                'expire' => 6000,
            ],
        ]);

        self::assertInstanceOf(CacheInterface::class, $adapter);
    }

    public function testConnectionWithFilesystemManagerAndCacheManager(): void
    {
        $manager      = $this->mock(FilesystemManager::class);
        $cacheManager = $this->mock(CacheManagerContract::class);
        $cacheManager->shouldReceive('hasDriver')
            ->once()
            ->with('array')
            ->andReturn(true);
        $cacheManager->shouldReceive('getDriver')
            ->once()
            ->andReturn(new ArrayCachePool());

        $cache = new CachedFactory($manager, $cacheManager);

        $adapter = $cache->getConnection([
            'cache' => [
                'driver' => 'array',
                'name'   => 'array',
                'key'    => 'test',
                'expire' => 6000,
            ],
        ]);

        self::assertInstanceOf(CacheInterface::class, $adapter);
    }
}
