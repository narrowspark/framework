<?php
namespace Viserio\Cache\Adapter;

use Psr\Cache\CacheItemPoolInterface;
use Cache\Adapter\Common\AbstractCachePool;
use Psr\Cache\CacheItemInterface;

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
    }

    protected function clearAllObjectsFromCache()
    {
    }

    protected function clearOneObjectFromCache($key)
    {
    }

    protected function storeItemInCache($key, CacheItemInterface $item, $ttl)
    {
    }
}
