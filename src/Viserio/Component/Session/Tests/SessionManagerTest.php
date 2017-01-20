<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Defuse\Crypto\Key;
use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contracts\Session\Store as StoreContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Session\SessionManager;

class SessionManagerTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        $this->manager = null;

        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testCookieStore()
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
                    'drivers' => [
                    ],
                    'cookie' => '',
                    'lifetime' => 5
                ],
                'cache' => [
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ]);
        $manager = $this->getSessionManager($config);
        $session = $manager->getDriver('cookie');

        $session->setRequestOnHandler($this->mock(ServerRequestInterface::class));

        self::assertInstanceOf(StoreContract::class, $session);
        self::assertTrue($session->handlerNeedsRequest());
    }

    public function testArrayStore()
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
                    'drivers' => [
                    ],
                    'cookie' => 'test',
                    'lifetime' => 5
                ],
                'cache' => [
                    'drivers'   => [],
                    'namespace' => false,
                ],
            ]);
        $manager = $this->getSessionManager($config);
        $session = $manager->getDriver('array');

        self::assertInstanceOf(StoreContract::class, $session);
    }

    private function getSessionManager($config)
    {
        return new SessionManager(
            new ArrayContainer([
                RepositoryContract::class   => $config,
                JarContract::class          => $this->mock(JarContract::class),
                CacheManagerContract::class => new CacheManager($config),
            ]),
            new Encrypter(Key::createNewRandomKey())
        );
    }
}
