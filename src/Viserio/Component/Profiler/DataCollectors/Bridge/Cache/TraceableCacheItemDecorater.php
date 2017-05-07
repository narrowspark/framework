<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollectors\Bridge\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Ported from.
 *
 * @link Symfony\Component\Cache\Adapter\TraceableAdapter
 */
final class TraceableCacheItemDecorater implements CacheItemPoolInterface
{
    /**
     * @var array
     */
    private $calls = [];

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $pool;

    /**
     * Original class name.
     *
     * @var string
     */
    private $name;

    /**
     * RecordingAdapter constructor.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $pool
     */
    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->name = get_class($pool);
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key)
    {
        $event = $this->start(__FUNCTION__);

        try {
            $item = $this->pool->getItem($key);
        } finally {
            $event->end = microtime(true);
        }

        if ($event->result[$key] = $item->isHit()) {
            ++$event->hits;
        } else {
            ++$event->misses;
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
            $event->end = microtime(true);
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
            $event->end = microtime(true);
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
            $event->end = microtime(true);
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
            $event->end = microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        $event = $this->start(__FUNCTION__, $keys);

        try {
            $result = $this->pool->getItems($keys);
        } finally {
            $event->end = microtime(true);
        }

        $f = function () use ($result, $event) {
            $event->result = [];

            foreach ($result as $key => $item) {
                if ($event->result[$key] = $item->isHit()) {
                    ++$event->hits;
                } else {
                    ++$event->misses;
                }

                yield $key => $item;
            }
        };

        return $f();
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result = $this->pool->clear();
        } finally {
            $event->end = microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys)
    {
        $event = $this->start(__FUNCTION__, $keys);

        try {
            return $event->result = $this->pool->deleteItems($keys);
        } finally {
            $event->end = microtime(true);
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
            $event->end = microtime(true);
        }
    }

    /**
     * Get all calls.
     *
     * @return array
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * Get the original class name.
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Start new event.
     *
     * @param string $name
     *
     * @return object
     */
    private function start(string $name)
    {
        $this->calls[] = $event = new class() {
            public $name;
            public $start;
            public $end;
            public $result;
            public $hits   = 0;
            public $misses = 0;
        };

        $event->name  = $name;
        $event->start = microtime(true);

        return $event;
    }
}
