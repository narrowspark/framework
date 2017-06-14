<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache;

use Psr\SimpleCache\CacheInterface;
use stdClass;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTrait;

/**
 * Ported from.
 *
 * @link Symfony\Component\Cache\Simple\TraceableCache
 */
class SimpleTraceableCacheDecorator implements CacheInterface
{
    use SimpleTraceableCacheDecoratorTrait;

    /**
     * A instance of psr16 cache.
     *
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $pool;

    /**
     * Instance of stdClass.
     *
     * @var \stdClass
     */
    private $miss;

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
        $this->name = get_class($pool);
        $this->miss = new stdClass();
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
