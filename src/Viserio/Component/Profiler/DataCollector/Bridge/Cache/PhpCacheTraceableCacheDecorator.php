<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

use Cache\TagInterop\TaggableCacheItemPoolInterface;
use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTrait;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits\TraceableCacheItemDecoratorTrait;

final class PhpCacheTraceableCacheDecorator implements CacheDecorator, CacheInterface, TaggableCacheItemPoolInterface
{
    use SimpleTraceableCacheDecoratorTrait;
    use TraceableCacheItemDecoratorTrait;

    /**
     * A instance of psr16 cache.
     *
     * @var \Cache\TagInterop\TaggableCacheItemPoolInterface|\Psr\SimpleCache\CacheInterface
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
     * @param \Cache\Adapter\Common\PhpCachePool|\Psr\SimpleCache\CacheInterface $pool
     */
    public function __construct($pool)
    {
        $this->pool = $pool;
        $this->name = \get_class($pool);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * Start new event.
     *
     * @return \Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCollector
     */
    private function start(string $name): object
    {
        $this->calls[] = $event = new TraceableCollector();

        $event->name = $name;
        $event->start = \microtime(true);

        return $event;
    }
}
