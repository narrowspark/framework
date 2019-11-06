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

namespace Viserio\Component\Cache\Tests;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Namespaced\NamespacedCachePool;
use League\Flysystem\Adapter\Local;
use Mockery;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Cache\CacheManager;

/**
 * @internal
 *
 * @small
 */
final class CacheManagerTest extends MockeryTestCase
{
    public function testArrayPoolCall(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'drivers' => [],
                    'namespace' => false,
                ],
            ],
        ]);

        self::assertInstanceOf(ArrayCachePool::class, $manager->getDriver('array'));
    }

    public function testArrayPoolCallWithLog(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'default' => 'array',
                    'drivers' => [],
                    'namespace' => false,
                ],
            ],
        ]);

        $logger = Mockery::mock(PsrLoggerInterface::class);

        $manager->setLogger($logger);

        self::assertInstanceOf(ArrayCachePool::class, $manager->getDriver('array'));
    }

    public function testNamespacedArrayPoolCall(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'default' => 'array',
                    'drivers' => [],
                    'namespace' => 'viserio',
                ],
            ],
        ]);

        self::assertInstanceOf(NamespacedCachePool::class, $manager->getDriver('array'));
    }

    public function testNamespacedNullPoolCall(): void
    {
        $manager = new CacheManager([
            'viserio' => [
                'cache' => [
                    'default' => 'null',
                    'drivers' => [],
                    'namespace' => 'viserio',
                ],
            ],
        ]);

        self::assertInstanceOf(NamespacedCachePool::class, $manager->getDriver('null'));
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
            'local' => new Local(__DIR__ . \DIRECTORY_SEPARATOR),
        ]));

        self::assertInstanceOf(FilesystemCachePool::class, $manager->getDriver('filesystem'));
    }
}
