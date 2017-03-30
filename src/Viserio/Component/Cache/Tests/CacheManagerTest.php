<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Tests;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\Encryption\EncryptedCachePool;
use Cache\Namespaced\NamespacedCachePool;
use Defuse\Crypto\Key;
use League\Flysystem\Adapter\Local;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

class CacheManagerTest extends MockeryTestCase
{
    public function testArrayPoolCall()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'cache' => [
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ]);
        $manager = new CacheManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(ArrayCachePool::class, $manager->getDriver('array'));
    }

    public function testArrayPoolCallWithLog()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ]);
        $manager = new CacheManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        $manager->setLogger($this->mock(PsrLoggerInterface::class));

        self::assertInstanceOf(ArrayCachePool::class, $manager->getDriver('array'));
    }

    public function testNamespacedArrayPoolCall()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => 'viserio',
                ],
            ]);
        $manager = new CacheManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(NamespacedCachePool::class, $manager->getDriver('array'));
    }

    public function testNamespacedNullPoolCall()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'cache' => [
                    'default'   => 'null',
                    'drivers'   => [],
                    'namespace' => 'viserio',
                ],
            ]);
        $manager = new CacheManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(NamespacedCachePool::class, $manager->getDriver('null'));
    }

    public function testFilesystem()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'cache' => [
                    'default' => 'null',
                    'drivers' => [
                        'filesystem' => [
                            'connection' => 'local',
                        ],
                    ],
                    'namespace' => false,
                ],
            ]);
        $manager = new CacheManager(
            new ArrayContainer([
                'local'                   => new Local(__DIR__ . '/'),
                RepositoryContract::class => $config,
            ])
        );

        self::assertInstanceOf(FilesystemCachePool::class, $manager->getDriver('filesystem'));
    }
}
