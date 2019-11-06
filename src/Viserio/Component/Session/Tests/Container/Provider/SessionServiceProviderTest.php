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

namespace Viserio\Component\Session\Tests\Container\Provider;

use ParagonIE\Halite\KeyFactory;
use Viserio\Component\Container\ContainerBuilder;
use Viserio\Component\Container\Tester\AbstractContainerTestCase;
use Viserio\Component\Session\Container\Provider\SessionServiceProvider;
use Viserio\Component\Session\SessionManager;
use Viserio\Contract\Session\Store as StoreContract;

/**
 * @internal
 *
 * @small
 */
final class SessionServiceProviderTest extends AbstractContainerTestCase
{
    /** @var string */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . 'test_key';

        KeyFactory::save(KeyFactory::generateEncryptionKey(), $this->path);

        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        \unlink($this->path);
    }

    public function testProvider(): void
    {
        self::assertInstanceOf(SessionManager::class, $this->container->get(SessionManager::class));
        self::assertInstanceOf(SessionManager::class, $this->container->get('session'));
        self::assertInstanceOf(StoreContract::class, $this->container->get(StoreContract::class));
        self::assertInstanceOf(StoreContract::class, $this->container->get('session.store'));
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareContainerBuilder(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->bind('config', [
            'viserio' => [
                'session' => [
                    'default' => 'file',
                    'env' => 'local',
                    'lifetime' => 3000,
                    'key_path' => $this->path,
                    'drivers' => [
                        'file' => [
                            'path' => __DIR__ . \DIRECTORY_SEPARATOR . 'session',
                        ],
                    ],
                ],
            ],
        ]);

        $containerBuilder->setParameter('container.dumper.inline_factories', true);
        $containerBuilder->setParameter('container.dumper.inline_class_loader', false);
        $containerBuilder->setParameter('container.dumper.as_files', true);

        $containerBuilder->register(new SessionServiceProvider());
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
