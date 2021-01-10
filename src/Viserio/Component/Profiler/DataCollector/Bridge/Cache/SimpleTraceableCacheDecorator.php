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

use Psr\SimpleCache\CacheInterface;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTrait;

/**
 * Ported from symfony, see original.
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Cache/Simple/TraceableCache.php
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */
final class SimpleTraceableCacheDecorator implements CacheDecorator, CacheInterface
{
    use SimpleTraceableCacheDecoratorTrait;

    /**
     * A instance of psr16 cache.
     *
     * @var \Psr\SimpleCache\CacheInterface
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
     * Create new Simple Traceable Cache Decorator instance.
     */
    public function __construct(CacheInterface $pool)
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
