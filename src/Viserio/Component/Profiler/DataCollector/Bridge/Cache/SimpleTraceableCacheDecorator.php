<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
     *
     * @param CacheInterface $pool
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
     * @param string $name
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
