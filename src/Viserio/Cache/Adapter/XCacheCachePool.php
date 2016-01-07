<?php
namespace Viserio\Cache\Adapter;

use Cache\Adapter\Common\AbstractCachePool;
use Psr\Cache\CacheItemInterface;

class XCacheCachePool extends AbstractCachePool
{
    protected function fetchObjectFromCache($key)
    {
        return xcache_get($key);
    }

    protected function clearAllObjectsFromCache()
    {
        xcache_clear_cache(XC_TYPE_VAR);

        return true;
    }

    protected function clearOneObjectFromCache($key)
    {
        xcache_unset($key);

        return true;
    }

    protected function storeItemInCache($key, CacheItemInterface $item, $ttl)
    {
        return xcache_set($key, $item, $ttl);
    }
}
