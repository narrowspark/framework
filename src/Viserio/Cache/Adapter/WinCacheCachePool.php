<?php
namespace Viserio\Cache\Adapter;

use Cache\Adapter\Common\AbstractCachePool;
use Psr\Cache\CacheItemInterface;

class WinCacheCachePool extends AbstractCachePool
{
    protected function fetchObjectFromCache($key)
    {
        return wincache_ucache_get($key);
    }

    protected function clearAllObjectsFromCache()
    {
        wincache_ucache_clear();

        return true;
    }

    protected function clearOneObjectFromCache($key)
    {
        wincache_ucache_delete($key);

        return true;
    }

    protected function storeItemInCache($key, CacheItemInterface $item, $ttl)
    {
        return wincache_ucache_set($key, $item, $ttl);
    }
}
