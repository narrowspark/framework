<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem\Tests\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use League\Flysystem\Adapter\Local as FlyLocal;
use League\Flysystem\Cached\CacheInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Filesystem\Cache\CachedFactory;
use Viserio\Component\Filesystem\FilesystemManager;
use Viserio\Contract\Cache\Manager as CacheManagerContract;

/**
 * @internal
 *
 * @small
 */
final class CachedFactoryTest extends MockeryTestCase
{
    public function testConnectionThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('A driver must be specified.');

        $cache = new CachedFactory(\Mockery::mock(FilesystemManager::class));

        $cache->getConnection(['test']);
    }

    public function testCreateConnectorThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported driver [local].');

        $manager = \Mockery::mock(FilesystemManager::class);
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
        $manager = \Mockery::mock(FilesystemManager::class);
        $manager->shouldReceive('hasConnection')
            ->once()
            ->with('local')
            ->andReturn(true);
        $manager->shouldReceive('createConnection')
            ->once()
            ->andReturn(\Mockery::mock(FlyLocal::class));

        $cache = new CachedFactory($manager);

        $adapter = $cache->getConnection([
            'cache' => [
                'driver' => 'local',
                'name' => 'local',
                'key' => 'test',
                'expire' => 6000,
            ],
        ]);

        self::assertInstanceOf(CacheInterface::class, $adapter);
    }

    public function testConnectionWithFilesystemManagerAndCacheManager(): void
    {
        $manager = \Mockery::mock(FilesystemManager::class);
        $cacheManager = \Mockery::mock(CacheManagerContract::class);
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
                'name' => 'array',
                'key' => 'test',
                'expire' => 6000,
            ],
        ]);

        self::assertInstanceOf(CacheInterface::class, $adapter);
    }
}
