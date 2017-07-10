<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

use Cache\Adapter\Common\PhpCachePool as PhpCachePoolInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTrait;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits\TraceableCacheItemDecoratorTrait;

final class PhpCacheTraceableCacheDecorator implements CacheInterface, PhpCachePoolInterface
{
    use SimpleTraceableCacheDecoratorTrait;
    use TraceableCacheItemDecoratorTrait;

    /**
     * A instance of psr16 cache.
     *
     * @var \Psr\Cache\CacheItemPoolInterface|\Psr\SimpleCache\CacheInterface
     */
    private $pool;

    /**
     * List of event calls.
     *
     * @var array
     */
    private $calls = [];

    /**
     * Original class name.
     *
     * @var string
     */
    private $name;

    /**
     * Create new Php Cache Traceable Cache Decorator instance.
     *
     * @param \Psr\Cache\CacheItemPoolInterface|\Psr\SimpleCache\CacheInterface $pool
     */
    public function __construct($pool)
    {
        $this->pool = $pool;
        $this->name = \get_class($pool);
    }

    /**
     * Get the original class name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): ?bool
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result = $this->pool->clear();
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTags(array $tags): bool
    {
        $event = $this->start(__FUNCTION__);

        try {
            $bool = $event->result = $this->pool->invalidateTags($tags);
        } finally {
            $event->end = \microtime(true);
        }

        return $bool;
    }

    /**
     * {@inheritdoc}
     */
    public function invalidateTag($tag)
    {
        $event = $this->start(__FUNCTION__);

        try {
            $bool = $event->result = $this->pool->invalidateTag($tag);
        } finally {
            $event->end = \microtime(true);
        }

        return $bool;
    }

    /**
     * Get a list of calls.
     *
     * @return array
     */
    public function getCalls(): array
    {
        try {
            return $this->calls;
        } finally {
            $this->calls = [];
        }
    }

    /**
     * Start new event.
     *
     * @param string $name
     *
     * @return object
     */
    private function start(string $name): object
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
        $event->start = \microtime(true);

        return $event;
    }
}
