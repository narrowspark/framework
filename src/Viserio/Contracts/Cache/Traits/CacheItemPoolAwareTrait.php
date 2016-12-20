<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cache\Traits;

use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;

trait CacheItemPoolAwareTrait
{
    /**
     * A CacheItemPool instance.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cachePool;

    /**
     * Set a CacheItemPool.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cachePool
     *
     * @return $this
     */
    public function setCacheItemPool(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;

        return $this;
    }

    /**
     * Get the CacheItemPool.
     *
     * @throws \RuntimeException
     *
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    public function getCacheItemPool(): CacheItemPoolInterface
    {
        if (!$this->cachePool) {
            throw new RuntimeException('Instance implementing \Psr\Cache\CacheItemPoolInterface is not set up.');
        }

        return $this->cachePool;
    }
}
