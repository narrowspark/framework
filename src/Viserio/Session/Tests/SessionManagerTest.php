<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Cache\CacheManager;
use Viserio\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Contracts\Session\Store as StoreContract;
use Viserio\Encryption\Encrypter;
use Viserio\Session\SessionManager;

class SessionManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $encrypter = new Encrypter(Key::createNewRandomKey());
        $config = $this->mock(ConfigContract::class);
        $config->shouldReceive('get')
            ->with('cache.drivers', []);
        $config->shouldReceive('get')
            ->with('cache.namespace');

        $manager = new SessionManager($config, $encrypter);
        $manager->setContainer(new ArrayContainer([
            JarContract::class => $this->mock(JarContract::class),
            CacheManagerContract::class => new CacheManager($config),
        ]));

        $this->manager = $manager;
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

        $this->assertInstanceOf(StoreContract::class, $session);
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

        $this->assertInstanceOf(StoreContract::class, $session);
    }
}
