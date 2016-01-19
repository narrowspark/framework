<?php
namespace Viserio\Cache\Adapter;

use Cache\Adapter\Common\AbstractCachePool;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class XCacheCachePool extends AbstractCachePool
{
    /**
     * Instance of implemented CacheItemPoolInterface.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $pool;

    /**
     * Construct.
     *
     * @param CacheItemPoolInterface $pool
     */
    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    protected function fetchObjectFromCache($key)
    {
        return $this->pool->getItem($key);
    }

    protected function clearAllObjectsFromCache()
    {
        return $this->pool->clear();
    }

    protected function clearOneObjectFromCache($key)
    {
        return $this->pool->deleteItem($key);
    }

    protected function storeItemInCache($key, CacheItemInterface $item, $ttl)
    {
    }
}
