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

namespace Viserio\Component\Cache\Tests\Container\Provider;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Cache\Container\Provider\CacheServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;

/**
 * @internal
 *
 * @small
 */
final class CacheServiceProviderTest extends AbstractContainerTestCase
{
    public function testProvider(): void
    {
        self::assertInstanceOf(CacheManager::class, $this->container->get(CacheManager::class));
        self::assertInstanceOf(CacheManager::class, $this->container->get('cache'));

        self::assertInstanceOf(ArrayCachePool::class, $this->container->get('cache.store'));
        self::assertInstanceOf(CacheItemPoolInterface::class, $this->container->get('cache.store'));
        self::assertInstanceOf(CacheItemPoolInterface::class, $this->container->get(CacheItemPoolInterface::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->register(new CacheServiceProvider());
        $containerBuilder->bind('config', [
            'viserio' => [
                'cache' => [
                    'default' => 'array',
                    'drivers' => [],
                    'namespace' => false,
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDumpFolderPath(): string
    {
        return __DIR__ . \DIRECTORY_SEPARATOR . 'Compiled';
    }

    /**
     * {@inheritdoc}
     */
    protected function getNamespace(): string
    {
        return __NAMESPACE__ . '\\Compiled';
    }
}
