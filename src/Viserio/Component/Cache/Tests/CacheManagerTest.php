<?php
declare(strict_types=1);
namespace Viserio\Component\Cache\Tests;

use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\Namespaced\NamespacedCachePool;
use Interop\Container\ContainerInterface;
use League\Flysystem\Adapter\Local;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Viserio\Component\Cache\CacheManager;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

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

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
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
            ->with('cache.namespace', false)
            ->andReturn(false);

        self::assertInstanceOf(ArrayCachePool::class, $this->manager->driver('array'));
    }

    public function testArrayPoolCallWithLog()
    {
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.drivers', []);
        $this->manager->getConfig()
            ->shouldReceive('get')
            ->once()
            ->with('cache.namespace', false)
            ->andReturn(false);

        $this->manager->setLogger($this->mock(PsrLoggerInterface::class));

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
            ->with('cache.namespace', false)
            ->andReturn('viserio');

        self::assertInstanceOf(NamespacedCachePool::class, $this->manager->driver('array'));
    }

    public function testNamespacedNullPoolCall()
    {
        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.drivers', []);

        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.namespace', false)
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
            ->with('cache.namespace', false)
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
            ->with('cache.namespace', false)
            ->andReturn(false);

        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')
            ->once()
            ->with('local')
            ->andReturn(new Local(__DIR__ . '/'));

        $this->manager->setContainer($container);

        self::assertInstanceOf(FilesystemCachePool::class, $this->manager->driver('filesystem'));
    }
}
