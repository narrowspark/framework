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

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $encrypter = new Encrypter(Key::createNewRandomKey());
        $config    = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->with('cache.drivers', []);
        $config->shouldReceive('get')
            ->with('cache.namespace', false);

        $manager = new SessionManager($config, $encrypter);
        $manager->setContainer(new ArrayContainer([
            JarContract::class          => $this->mock(JarContract::class),
            CacheManagerContract::class => new CacheManager($config),
        ]));

        $this->manager = $manager;
    }

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
        $manager = $this->manager;
        $manager->getConfig()
            ->shouldReceive('get')
            ->with('session.drivers', [])
            ->once();
        $manager->getConfig()
            ->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('test');
        $manager->getConfig()
            ->shouldReceive('get')
            ->with('session.lifetime')
            ->once()
            ->andReturn(5);
        $session = $manager->driver('cookie');

        $session->setRequestOnHandler($this->mock(ServerRequestInterface::class));

        self::assertInstanceOf(StoreContract::class, $session);
        self::assertTrue($session->handlerNeedsRequest());
    }

    public function testArrayStore()
    {
        $manager = $this->manager;
        $manager->getConfig()
            ->shouldReceive('get')
            ->with('session.drivers', [])
            ->once();
        $manager->getConfig()
            ->shouldReceive('get')
            ->with('session.lifetime')
            ->once()
            ->andReturn(5);
        $manager->getConfig()
            ->shouldReceive('get')
            ->with('session.cookie', '')
            ->once()
            ->andReturn('test');
        $session = $manager->driver('array');

        self::assertInstanceOf(StoreContract::class, $session);
    }
}
