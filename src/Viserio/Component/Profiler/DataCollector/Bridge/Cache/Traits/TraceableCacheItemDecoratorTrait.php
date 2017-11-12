<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits;

use Psr\Cache\CacheItemInterface;

trait TraceableCacheItemDecoratorTrait
{
    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $event = $this->start(__FUNCTION__);

        try {
            $item = $this->pool->getItem($key);
        } finally {
            $event->end = \microtime(true);
        }

        if ($event->result[$key] = $item->isHit()) {
            $event->hits++;
        } else {
            $event->misses++;
        }

        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key)
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result[$key] = $this->pool->hasItem($key);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key)
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result[$key] = $this->pool->deleteItem($key);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item)
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result[$item->getKey()] = $this->pool->save($item);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result[$item->getKey()] = $this->pool->saveDeferred($item);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = []): \Generator
    {
        $event = $this->start(__FUNCTION__);

        try {
            $result = $this->pool->getItems($keys);
        } finally {
            $event->end = \microtime(true);
        }

        $f = function () use ($result, $event) {
            $event->result = [];

            foreach ($result as $key => $item) {
                if ($event->result[$key] = $item->isHit()) {
                    $event->hits++;
                } else {
                    $event->misses++;
                }

                yield $key => $item;
            }
        };

        return $f();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result = $this->pool->deleteItems($keys);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function commit()
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result = $this->pool->commit();
        } finally {
            $event->end = \microtime(true);
        }
    }
}
