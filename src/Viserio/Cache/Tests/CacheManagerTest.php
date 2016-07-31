<?php
declare(strict_types=1);
namespace Viserio\Cache\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Cache\CacheManager;
use Viserio\Contracts\Config\Manager as ConfigManager;
use Cache\Adapter\PHPArray\ArrayCachePool;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    protected $manager;

    public function setUp()
    {
        $this->manager = new CacheManager(
            $this->mock(ConfigManager::class)
        );
    }

    public function testArrayPoolCall()
    {
        $this->manager->getConfig()->shouldReceive('get')
            ->once()
            ->with('cache.drivers', []);

        $this->assertInstanceOf(ArrayCachePool::class, $this->manager->driver('array'));
    }
}
