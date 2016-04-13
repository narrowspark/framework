<?php
namespace Viserio\Cache\Tests\Adapter;

use Cache\IntegrationTests\CachePoolTest;
use Viserio\Cache\Adapter\XCacheCachePool;

class XCacheCachePoolTest extends CachePoolTest
{
    public function setUp()
    {
        if (!function_exists('xcache_unset')) {
            $this->markTestSkipped('XCache module is not installed.');
        }

        parent::setUp();
    }

    public function createCachePool()
    {
        return new XCacheCachePool();
    }
}
