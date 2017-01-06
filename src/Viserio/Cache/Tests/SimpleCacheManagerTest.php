<?php
declare(strict_types=1);
namespace Viserio\Cache\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cache\SimpleCacheManager;
use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use PHPUnit\Framework\TestCase;
use Viserio\Contracts\Config\Repository as RepositoryContract;

class SimpleCacheManagerTest extends TestCase
{
    use MockeryTrait;

    protected $manager;

    public function setUp()
    {
        parent::setUp();

        $this->manager = new SimpleCacheManager(
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

    public function testGetSimpleCacheBridge()
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

        self::assertInstanceOf(SimpleCacheBridge::class, $this->manager->driver('array'));
    }
}
