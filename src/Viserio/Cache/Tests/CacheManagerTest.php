<?php
declare(strict_types=1);
namespace Viserio\Cache\Tests;

use Interop\Container\ContainerInterface;
use League\Flysystem\Adapter\Local;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cache\CacheManager;
use Viserio\Contracts\Config\Manager as ConfigManager;
use Cache\Adapter\{
    PHPArray\ArrayCachePool,
    Void\VoidCachePool,
    Chain\CachePoolChain,
    Filesystem\FilesystemCachePool
};
use Cache\SessionHandler\Psr6SessionHandler;
use Cache\Namespaced\NamespacedCachePool;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = new CacheManager(
            $this->mock(ConfigManager::class)
        );
    }

    public function testArrayPoolCall()
    {
        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.drivers', []);
        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.namespace')
            ->andReturn(null);

        $this->assertInstanceOf(ArrayCachePool::class, $this->manager->driver('array'));
    }

    public function testNamespacedArrayPoolCall()
    {
        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.drivers', []);

        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.namespace')
            ->andReturn('viserio');

        $this->assertInstanceOf(NamespacedCachePool::class, $this->manager->driver('array'));
    }

    public function testDontNamespceSessionHandler()
    {
        $this->manager->getConfig()->shouldReceive('get')
            ->twice()
            ->with('cache.drivers', [])
            ->andReturn([
                'session' => [
                    'pool' => 'array',
                    'config' => [],
                ]
            ]);
        $this->manager->getConfig()->shouldReceive('get')
            ->twice()
            ->with('cache.namespace')
            ->andReturn('viserio');

        $this->assertInstanceOf(Psr6SessionHandler::class, $this->manager->driver('session'));
    }

    public function testNamespacedNullPoolCall()
    {
        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.drivers', []);

        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.namespace')
            ->andReturn('viserio');

        $this->assertInstanceOf(NamespacedCachePool::class, $this->manager->driver('null'));
    }

    public function testChain()
    {
        $this->manager->getConfig()->shouldReceive('get')
            ->twice()
            ->with('cache.drivers', []);

        $this->manager->getConfig()->shouldReceive('get')
            ->twice()
            ->with('cache.namespace')
            ->andReturn('viserio');

        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.chain.options', [])
            ->andReturn([]);

        $chain = $this->manager->chain(['array', 'null', new VoidCachePool()]);

        $this->assertInstanceOf(CachePoolChain::class, $chain);
    }

    public function testFilesystem()
    {
        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.drivers', [])
            ->andReturn([
                'filesystem' => [
                    'connection' => 'local'
                ]
            ]);

        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.namespace')
            ->andReturn(null);

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with('local')
            ->andReturn(new Local(__DIR__ . '/'));

        $this->manager->setContainer($container);

        $this->assertInstanceOf(FilesystemCachePool::class, $this->manager->driver('filesystem'));
    }
}
