<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use League\Flysystem\Adapter\Local as FlyLocal;
use League\Flysystem\Cached\CacheInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Filesystem\Cache\CachedFactory;
use Viserio\Filesystem\FilesystemManager;

class CachedFactoryTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage A driver must be specified.
     */
    public function testConnectionThrowsInvalidArgumentException()
    {
        $cache = new CachedFactory($this->mock(FilesystemManager::class));

        $cache->connection(['test']);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Unsupported driver [local].
     */
    public function testCreateConnectorThrowsInvalidArgumentException()
    {
        $manager = $this->mock(FilesystemManager::class);
        $manager->shouldReceive('hasConnection')
            ->andReturn(false);
        $cache = new CachedFactory($manager);

        $cache->connection([
            'cache' => [
                'driver' => 'local',
            ],
        ]);
    }

    public function testConnectionWithFilesystemManager()
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

        $adapter = $cache->connection([
            'cache' => [
                'driver' => 'local',
                'name' => 'local',
                'key' => 'test',
                'expire' => 6000,
            ],
        ]);

        $this->assertInstanceOf(CacheInterface::class, $adapter);
    }

    public function testConnectionWithFilesystemManagerAndCacheManager()
    {
        $manager = $this->mock(FilesystemManager::class);
        $cacheManager = $this->mock(CacheManagerContract::class);
        $cacheManager->shouldReceive('hasDriver')
            ->once()
            ->with('array')
            ->andReturn(true);
        $cacheManager->shouldReceive('driver')
            ->once()
            ->andReturn(new ArrayCachePool());

        $cache = new CachedFactory($manager, $cacheManager);

        $adapter = $cache->connection([
            'cache' => [
                'driver' => 'array',
                'name' => 'array',
                'key' => 'test',
                'expire' => 6000,
            ],
        ]);

        $this->assertInstanceOf(CacheInterface::class, $adapter);
    }
}
