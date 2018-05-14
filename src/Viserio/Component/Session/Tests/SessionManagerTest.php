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

namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Session\Handler\MigratingSessionHandler;
use Viserio\Component\Session\SessionManager;
use Viserio\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Contract\Session\Exception\RuntimeException;
use Viserio\Contract\Session\Store as StoreContract;

/**
 * @internal
 *
 * @small
 */
final class SessionManagerTest extends MockeryTestCase
{
    /** @var string */
    private $keyPath;

    /** @var \Viserio\Component\Session\SessionManager */
    private $sessionManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->keyPath = __DIR__ . \DIRECTORY_SEPARATOR . 'session_key';

        $key = KeyFactory::generateEncryptionKey();

        KeyFactory::save($key, $this->keyPath);

        $this->sessionManager = new SessionManager([
            'viserio' => [
                'session' => [
                    'lifetime' => 5,
                    'env' => 'local',
                    'key_path' => $this->keyPath,
                    'drivers' => [
                        'migrating' => [
                            'current' => 'array',
                            'write_only' => 'array',
                        ],
                        'file' => [
                            'path' => __DIR__ . \DIRECTORY_SEPARATOR . 'session',
                        ],
                    ],
                ],
                'cache' => [
                    'drivers' => [],
                    'namespace' => false,
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        \unlink($this->keyPath);
    }

    public function testCookieStore(): void
    {
        $this->sessionManager->setCookieJar(\Mockery::mock(JarContract::class));

        $session = $this->sessionManager->getDriver('cookie');

        $session->setRequestOnHandler(\Mockery::mock(ServerRequestInterface::class));

        self::assertInstanceOf(StoreContract::class, $session);
        self::assertTrue($session->handlerNeedsRequest());
    }

    public function testCookieStoreThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No instance of [Viserio\\Contract\\Cookie\\QueueingFactory] found.');

        $this->sessionManager->getDriver('cookie');
    }

    public function testFilesystemStoreThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No instance of [Viserio\\Contract\\Cache\\Manager] found.');

        $this->sessionManager->getDriver('filesystem');
    }

    public function testArrayStore(): void
    {
        $session = $this->sessionManager->getDriver('array');

        self::assertInstanceOf(StoreContract::class, $session);
    }

    public function testMigratingStore(): void
    {
        $session = $this->sessionManager->getDriver('migrating');

        self::assertInstanceOf(StoreContract::class, $session);
        self::assertInstanceOf(MigratingSessionHandler::class, $session->getHandler());
    }

    public function testMigratingStoreThrowExceptionIfAConfigIsMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The MigratingSessionHandler needs a current and write only handler.');

        $manager = new SessionManager([
            'viserio' => [
                'session' => [
                    'lifetime' => 5,
                    'env' => 'local',
                    'key_path' => $this->keyPath,
                    'drivers' => [
                        'migrating' => [
                            'current' => 'array',
                        ],
                    ],
                ],
            ],
        ]);
        $session = $manager->getDriver('migrating');

        self::assertInstanceOf(StoreContract::class, $session);
        self::assertInstanceOf(MigratingSessionHandler::class, $session->getHandler());
    }
}
