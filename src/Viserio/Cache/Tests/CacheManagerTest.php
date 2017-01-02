<?php
declare(strict_types=1);
namespace Viserio\Cache\Tests;

use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\Namespaced\NamespacedCachePool;
use Cache\SessionHandler\Psr6SessionHandler;
use Interop\Container\ContainerInterface;
use League\Flysystem\Adapter\Local;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cache\CacheManager;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use PHPUnit\Framework\TestCase;

class CacheManagerTest extends TestCase
{
    use MockeryTrait;

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = new CacheManager(
            $this->mock(RepositoryContract::class)
        );
    }

    public function testArrayPoolCall()
    {
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.drivers', []);
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.namespace')
            ->andReturn(null);

        self::assertInstanceOf(ArrayCachePool::class, $this->manager->driver('array'));
    }

    public function testNamespacedArrayPoolCall()
    {
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.drivers', []);

        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.namespace')
            ->andReturn('viserio');

        self::assertInstanceOf(NamespacedCachePool::class, $this->manager->driver('array'));
    }

    public function testDontNamespceSessionHandler()
    {
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->twice()
            ->with('cache.drivers', [])
            ->andReturn([
                'session' => [
                    'pool'   => 'array',
                    'config' => [],
                ],
            ]);
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->twice()
            ->with('cache.namespace')
            ->andReturn('viserio');

        self::assertInstanceOf(Psr6SessionHandler::class, $this->manager->driver('session'));
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

        self::assertInstanceOf(NamespacedCachePool::class, $this->manager->driver('null'));
    }

    public function testChain()
    {
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->twice()
            ->with('cache.drivers', []);

        $this->manager->getConfig()
            ->shouldReceive('get')
            ->twice()
            ->with('cache.namespace')
            ->andReturn('viserio');

        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.chain.options', [])
            ->andReturn([]);

        $chain = $this->manager->chain(['array', 'null', new VoidCachePool()]);

        self::assertInstanceOf(CachePoolChain::class, $chain);
    }

    public function testFilesystem()
    {
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.drivers', [])
            ->andReturn([
                'filesystem' => [
                    'connection' => 'local',
                ],
            ]);

        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.namespace')
            ->andReturn(null);

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with('local')
            ->andReturn(new Local(__DIR__ . '/'));

        $this->manager->setContainer($container);

        self::assertInstanceOf(FilesystemCachePool::class, $this->manager->driver('filesystem'));
    }
}
