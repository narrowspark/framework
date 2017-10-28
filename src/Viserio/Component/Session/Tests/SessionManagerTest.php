<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Contract\Cache\Manager as CacheManagerContract;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\KeyFactory;
use Viserio\Component\Session\SessionManager;

class SessionManagerTest extends MockeryTestCase
{
    public function testCookieStore(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->twice()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->twice()
            ->with('viserio')
            ->andReturn([
                'session' => [
                    'drivers' => [
                    ],
                    'cookie'   => '',
                    'lifetime' => 5,
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

    public function testArrayStore(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->twice()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->twice()
            ->with('viserio')
            ->andReturn([
                'session' => [
                    'drivers' => [
                    ],
                    'cookie'   => 'test',
                    'lifetime' => 5,
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
        $pw  = \random_bytes(32);
        $key = KeyFactory::generateKey($pw);

        return new SessionManager(
            new ArrayContainer([
                RepositoryContract::class   => $config,
                JarContract::class          => $this->mock(JarContract::class),
                CacheManagerContract::class => new CacheManager(new ArrayContainer([
                    RepositoryContract::class => $config,
                ])),
                EncrypterContract::class => new Encrypter($key),
            ])
        );
    }
}
