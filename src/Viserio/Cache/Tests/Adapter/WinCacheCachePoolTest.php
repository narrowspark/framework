<?php
namespace Viserio\Cache\Tests\Adapter;

use Cache\IntegrationTests\CachePoolTest;
use Viserio\Cache\Adapter\WinCacheCachePool;

class WinCacheCachePoolTest extends CachePoolTest
{
    public function setUp()
    {
        if (!function_exists('wincache_ucache_clear')) {
            $this->markTestSkipped('WinCache module is not installed.');
        }

        parent::setUp();
    }

    public function createCachePool()
    {
        return new WinCacheCachePool();
    }
}
