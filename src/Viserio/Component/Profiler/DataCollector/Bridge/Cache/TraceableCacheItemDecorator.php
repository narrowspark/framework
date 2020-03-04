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

use Psr\Cache\CacheItemPoolInterface;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits\TraceableCacheItemDecoratorTrait;

/**
 * Ported from symfony, see original.
 *
 * @see https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Cache/Adapter/TraceableAdapter.php
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 */
final class TraceableCacheItemDecorator implements CacheDecorator, CacheItemPoolInterface
{
    use TraceableCacheItemDecoratorTrait;

    /**
     * List of event calls.
     *
     * @var array
     */
    private $calls = [];

    /**
     * A instance of the item pool.
     *
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
     * Create new Traceable Cache Item Decorator instance.
     */
    public function __construct(CacheItemPoolInterface $pool)
    {
        $this->name = \get_class($pool);
        $this->pool = $pool;
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
    private function start(string $name): TraceableCollector
    {
        $this->calls[] = $event = new TraceableCollector();

        $event->name = $name;
        $event->start = \microtime(true);

        return $event;
    }
}
