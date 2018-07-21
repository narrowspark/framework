<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contract\Session\Exception\RuntimeException;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Session\Handler\MigratingSessionHandler;
use Viserio\Component\Session\SessionManager;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

/**
 * @internal
 */
final class SessionManagerTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var string
     */
    private $keyPath;

    /**
     * @var \Viserio\Component\Session\SessionManager
     */
    private $sessionManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->keyPath = self::normalizeDirectorySeparator(__DIR__ . '/session_key');

        $key = KeyFactory::generateEncryptionKey();

        KeyFactory::save($key, $this->keyPath);

        $this->sessionManager = new SessionManager([
            'viserio' => [
                'session' => [
                    'lifetime' => 5,
                    'key_path' => $this->keyPath,
                    'drivers'  => [
                        'migrating' => [
                            'current'    => 'array',
                            'write_only' => 'array',
                        ],
                    ],
                ],
                'cache' => [
                    'drivers'   => [],
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
        $this->sessionManager->setCookieJar($this->mock(JarContract::class));

        $session = $this->sessionManager->getDriver('cookie');

        $session->setRequestOnHandler($this->mock(ServerRequestInterface::class));

        static::assertInstanceOf(StoreContract::class, $session);
        static::assertTrue($session->handlerNeedsRequest());
    }

    public function testCookieStoreThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No instance of [Viserio\\Component\\Contract\\Cookie\\QueueingFactory] found.');

        $this->sessionManager->getDriver('cookie');
    }

    public function testFilesystemStoreThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No instance of [Viserio\\Component\\Contract\\Cache\\Manager] found.');

        $this->sessionManager->getDriver('filesystem');
    }

    public function testArrayStore(): void
    {
        $session = $this->sessionManager->getDriver('array');

        static::assertInstanceOf(StoreContract::class, $session);
    }

    public function testMigratingStore(): void
    {
        $session = $this->sessionManager->getDriver('migrating');

        static::assertInstanceOf(StoreContract::class, $session);
        static::assertInstanceOf(MigratingSessionHandler::class, $session->getHandler());
    }

    public function testMigratingStoreThrowExceptionIfAConfigIsMissing(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('The MigratingSessionHandler needs a current and write only handler.');

        $manager = new SessionManager([
            'viserio' => [
                'session' => [
                    'lifetime' => 5,
                    'key_path' => $this->keyPath,
                    'drivers'  => [
                        'migrating' => [
                            'current'    => 'array',
                        ],
                    ],
                ],
            ],
        ]);
        $session = $manager->getDriver('migrating');

        static::assertInstanceOf(StoreContract::class, $session);
        static::assertInstanceOf(MigratingSessionHandler::class, $session->getHandler());
    }
}
