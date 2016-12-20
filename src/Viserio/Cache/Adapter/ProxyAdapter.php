<?php
declare(strict_types=1);
namespace Viserio\Cache\Adapter;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ProxyAdapter implements CacheItemPoolInterface
{
    /**
     * A instance of CacheItemPoolInterface.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $pool;

    /**
     * Hints counter.
     *
     * @var int
     */
    private $hits = 0;

    /**
     * Misses counter.
     *
     * @var int
     */
    private $misses = 0;

    /**
     * Create new proxy adapter.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $pool
     */
    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $item = $this->pool->getItem($key);

        if ($item->isHit()) {
            ++$this->hits;
        } else {
            ++$this->misses;
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        return $this->generateItems($this->pool->getItems($keys));
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        return $this->pool->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->pool->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        return $this->pool->deleteItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        return $this->pool->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        return $this->pool->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        return $this->pool->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        return $this->pool->commit();
    }

    /**
     * Returns the number of cache read hits.
     *
     * @return int
     */
    public function getHits(): int
    {
        return $this->hits;
    }

    /**
     * Returns the number of cache read misses.
     *
     * @return int
     */
    public function getMisses(): int
    {
        return $this->misses;
    }

    /**
     * Count hints or misses on array.
     *
     * @param array $items
     *
     * @return array
     */
    private function generateItems(array $items)
    {
        foreach ($items as $key => $item) {
            if ($item->isHit()) {
                ++$this->hits;
            } else {
                ++$this->misses;
            }
        }

        return $items;
    }
}
