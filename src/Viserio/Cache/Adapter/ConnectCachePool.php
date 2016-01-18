<?php
namespace Viserio\Cache\Adapter;

use Cache\Adapter\Common\AbstractCachePool;
use Psr\Cache\CacheItemInterface;
use Viserio\Contracts\Connect\ConnectionFactory as ConnectionFactoryContract;

class PdoCachePool extends AbstractCachePool
{
    /**
     * The Viserio ConnectionFactory instance.
     *
     * @var \Viserio\Contracts\Connect\ConnectionFactory
     */
    private $connect;

    public function __construct(ConnectionFactoryContract $connect)
    {
        $this->connect = $connect;
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
