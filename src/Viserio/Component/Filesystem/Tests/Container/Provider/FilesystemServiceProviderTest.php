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

namespace Viserio\Component\Filesystem\Tests\Container\Provider;

use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Viserio\Component\Cache\Container\Provider\CacheServiceProvider;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Filesystem\Cache\CachedFactory;
use Viserio\Component\Filesystem\Container\Provider\FilesystemServiceProvider;
use Viserio\Component\Filesystem\FilesystemAdapter;
use Viserio\Component\Filesystem\FilesystemManager;

/**
 * @internal
 *
 * @small
 */
final class FilesystemServiceProviderTest extends AbstractContainerTestCase
{
    private const LOCAL_PATH = __DIR__ . \DIRECTORY_SEPARATOR . 'test';

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        @\mkdir(self::LOCAL_PATH);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        @\rmdir(self::LOCAL_PATH);
    }

    public function testProvider(): void
    {
        self::assertInstanceOf(FilesystemManager::class, $this->container->get(FilesystemManager::class));
        self::assertInstanceOf(FilesystemManager::class, $this->container->get(Filesystem::class));
        self::assertInstanceOf(FilesystemManager::class, $this->container->get(FilesystemInterface::class));
        self::assertInstanceOf(FilesystemManager::class, $this->container->get('flysystem'));
        self::assertInstanceOf(FilesystemAdapter::class, $this->container->get('flysystem.connection'));
        self::assertInstanceOf(CachedFactory::class, $this->container->get(CachedFactory::class));
        self::assertInstanceOf(CachedFactory::class, $this->container->get('flysystem.cached.factory'));

        self::assertInstanceOf(FilesystemManager::class, $this->container->get(FilesystemManager::class));
        self::assertInstanceOf(CachedFactory::class, $this->container->get(CachedFactory::class));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind('config', [
            'viserio' => [
                'filesystem' => [
                    'default' => 'local',
                    'connections' => [
                        'local' => [
                            'path' => self::LOCAL_PATH,
                            'prefix' => 'your-prefix',
                        ],
                    ],
                ],
                'cache' => [
                    'default' => 'array',
                    'drivers' => [],
                    'namespace' => false,
                ],
            ],
        ]);
        $containerBuilder->setParameter('container.dumper.inline_factories', true);
        $containerBuilder->setParameter('container.dumper.inline_class_loader', false);
        $containerBuilder->setParameter('container.dumper.as_files', true);

        $containerBuilder->register(new FilesystemServiceProvider());
        $containerBuilder->register(new CacheServiceProvider());
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
