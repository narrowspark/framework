<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use ParagonIE\Halite\KeyFactory;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
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
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->keyPath = self::normalizeDirectorySeparator(__DIR__ . '/session_key');

        $key = KeyFactory::generateEncryptionKey();

        KeyFactory::save($key, $this->keyPath);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        \unlink($this->keyPath);
    }

    public function testCookieStore(): void
    {
        $manager = $this->getSessionManager();

        $manager->setCookieJar($this->mock(JarContract::class));

        $session = $manager->getDriver('cookie');

        $session->setRequestOnHandler($this->mock(ServerRequestInterface::class));

        static::assertInstanceOf(StoreContract::class, $session);
        static::assertTrue($session->handlerNeedsRequest());
    }

    public function testCookieStoreThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Session\Exception\RuntimeException::class);
        $this->expectExceptionMessage('No instance of [Viserio\\Component\\Contract\\Cookie\\QueueingFactory] found.');

        $manager = $this->getSessionManager();
        $manager->getDriver('cookie');
    }

    public function testFilesystemStoreThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Session\Exception\RuntimeException::class);
        $this->expectExceptionMessage('No instance of [Viserio\\Component\\Contract\\Cache\\Manager] found.');

        $manager = $this->getSessionManager();
        $manager->getDriver('filesystem');
    }

    public function testArrayStore(): void
    {
        $manager = $this->getSessionManager();
        $session = $manager->getDriver('array');

        static::assertInstanceOf(StoreContract::class, $session);
    }

    public function testMigratingStore(): void
    {
        $manager = $this->getSessionManager();
        $session = $manager->getDriver('migrating');

        static::assertInstanceOf(StoreContract::class, $session);
        static::assertInstanceOf(MigratingSessionHandler::class, $session->getHandler());
    }

    public function testMigratingStoreThrowExceptionIfAConfigIsMissing(): void
    {
        $this->expectException(\Viserio\Component\Contract\Session\Exception\RuntimeException::class);
        $this->expectExceptionMessage('The MigratingSessionHandler needs a current and write only handler.');

        $manager = new SessionManager(
            new ArrayContainer([
                'config' => [
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
                ],
            ])
        );
        $session = $manager->getDriver('migrating');

        static::assertInstanceOf(StoreContract::class, $session);
        static::assertInstanceOf(MigratingSessionHandler::class, $session->getHandler());
    }

    private function getSessionManager()
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
            ]);

        return new SessionManager(
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );
    }
}
