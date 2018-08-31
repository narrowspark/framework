<?php
declare(strict_types=1);
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
final class TraceableCacheItemDecorator implements CacheItemPoolInterface, CacheDecorator
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
     *
     * @param \Psr\Cache\CacheItemPoolInterface $pool
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
     * @param string $name
     *
     * @return \Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCollector
     */
    private function start(string $name): TraceableCollector
    {
        $this->calls[] = $event = new TraceableCollector();

        $event->name  = $name;
        $event->start = \microtime(true);

        return $event;
    }
}
