<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Tests;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Namespaced\NamespacedCachePool;
use League\Flysystem\Adapter\Local;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Cache\CacheManager;

/**
 * @internal
 */
final class CacheManagerTest extends MockeryTestCase
{
    public function testArrayPoolCall(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ],
        ]);

        static::assertInstanceOf(ArrayCachePool::class, $manager->getDriver('array'));
    }

    public function testArrayPoolCallWithLog(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ],
        ]);

        $manager->setLogger($this->mock(PsrLoggerInterface::class));

        static::assertInstanceOf(ArrayCachePool::class, $manager->getDriver('array'));
    }

    public function testNamespacedArrayPoolCall(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'default'   => 'array',
                    'drivers'   => [],
                    'namespace' => 'viserio',
                ],
            ],
        ]);

        static::assertInstanceOf(NamespacedCachePool::class, $manager->getDriver('array'));
    }

    public function testNamespacedNullPoolCall(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'default'   => 'null',
                    'drivers'   => [],
                    'namespace' => 'viserio',
                ],
            ],
        ]);

        static::assertInstanceOf(NamespacedCachePool::class, $manager->getDriver('null'));
    }

    public function testFilesystem(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'default' => 'null',
                    'drivers' => [
                        'filesystem' => [
                            'connection' => 'local',
                        ],
                    ],
                    'namespace' => false,
                ],
            ],
        ]);
        $manager->setContainer(new ArrayContainer([
            'local' => new Local(__DIR__ . '/'),
        ]));

        static::assertInstanceOf(FilesystemCachePool::class, $manager->getDriver('filesystem'));
    }
}
