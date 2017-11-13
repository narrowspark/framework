<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Session\SessionManager;

class SessionManagerTest extends MockeryTestCase
{
    /**
     * @var string
     */
    private $keyPath;

    public function setUp(): void
    {
        parent::setUp();

        $dir = __DIR__ . '/stubs';

        \mkdir($dir);

        $pw  = \random_bytes(32);
        $key = KeyFactory::generateKey($pw);

        KeyFactory::saveKeyToFile($dir . '/session_key', $key);

        $this->keyPath = $dir . '/session_key';
    }

    public function tearDown(): void
    {
        \unlink($this->keyPath);
        \rmdir(__DIR__ . '/stubs');

        parent::tearDown();
    }

    public function testCookieStore(): void
    {
        $manager = $this->getSessionManager();

        $manager->setCookieJar($this->mock(JarContract::class));

        $session = $manager->getDriver('cookie');

        $session->setRequestOnHandler($this->mock(ServerRequestInterface::class));

        self::assertInstanceOf(StoreContract::class, $session);
        self::assertTrue($session->handlerNeedsRequest());
    }

    /**
     * @expectedException \Viserio\Component\Contract\Session\Exception\RuntimeException
     * @expectedExceptionMessage No instance of [Viserio\Component\Contract\Cookie\QueueingFactory] found.
     */
    public function testCookieStoreThrowException(): void
    {
        $manager = $this->getSessionManager();
        $manager->getDriver('cookie');
    }

    /**
     * @expectedException \Viserio\Component\Contract\Session\Exception\RuntimeException
     * @expectedExceptionMessage No instance of [Viserio\Component\Contract\Cache\Manager] found.
     */
    public function testFilesystemStoreThrowException(): void
    {
        $manager = $this->getSessionManager();
        $manager->getDriver('filesystem');
    }

    public function testArrayStore(): void
    {
        $manager = $this->getSessionManager();
        $session = $manager->getDriver('array');

        self::assertInstanceOf(StoreContract::class, $session);
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
