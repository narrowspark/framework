<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors\Bridge\Recording;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use StdClass;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * An adapter that collects all your cache calls.
 *
 * @author Aaron Scherer <aequasi@gmail.com>
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
final class RecordingAdapter implements CacheItemPoolInterface
{
    /**
     * @var array
     */
    private $calls = [];

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch
     */
    private $stopwatch;

    /**
     * RecordingAdapter constructor.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cachePool
     */
    public function __construct(CacheItemPoolInterface $cachePool, Stopwatch $stopwatch)
    {
        $this->cachePool = $cachePool;
        $this->stopwatch = $stopwatch;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key): CacheItemInterface
    {
        $call        = $this->timeCall(__FUNCTION__, [$key]);
        $result      = $call->result;
        $call->isHit = $result->isHit();

        // Display the result in a good way depending on the data type
        if ($call->isHit) {
            $call->result = $this->getValueRepresentation($result);
        } else {
            $call->result = null;
        }

        $this->calls[] = $call;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key): bool
    {
        $call          = $this->timeCall(__FUNCTION__, [$key]);
        $this->calls[] = $call;

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key): bool
    {
        $call          = $this->timeCall(__FUNCTION__, [$key]);
        $this->calls[] = $call;

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $arg = $this->getValueRepresentation($item);

        $key             = $item->getKey();
        $call            = $this->timeCall(__FUNCTION__, [$item]);
        $call->arguments = [$arg];
        $this->calls[]   = $call;

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $arg = $this->getValueRepresentation($item);

        $key             = $item->getKey();
        $call            = $this->timeCall(__FUNCTION__, [$item]);
        $call->arguments = [$arg];
        $this->calls[]   = $call;

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        $call   = $this->timeCall(__FUNCTION__, [$keys]);
        $result = $call->result;
        $hits   = 0;
        $items  = [];

        foreach ($result as $item) {
            $items[] = $item;

            if ($item->isHit()) {
                +$hits;
            }
        }

        $call->result = $this->getValueRepresentation($items);
        $call->hits   = $hits;
        $call->count  = count($items);

        $this->calls[] = $call;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $call          = $this->timeCall(__FUNCTION__, []);
        $this->calls[] = $call;

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        $call          = $this->timeCall(__FUNCTION__, [$keys]);
        $this->calls[] = $call;

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        $call          = $this->timeCall(__FUNCTION__);
        $this->calls[] = $call;

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function getCalls(): array
    {
        return $this->calls;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return \StdClass
     */
    private function timeCall(string $name, array $arguments = []): StdClass
    {
        $time   = 0;
        $event  = $this->stopwatch->start(get_class($this->cachePool), 'cache');
        $result = call_user_func_array([$this->cachePool, $name], $arguments);

        if ($event->isStarted()) {
            $event->stop();
            $time = $event->getEndTime() - $event->getStartTime();
        }

        $object = (object) compact('name', 'arguments', 'start', 'time', 'result');

        return $object;
    }

    /**
     * Get a string to represent the value.
     *
     * @param mixed $value
     *
     * @return string
     */
    private function getValueRepresentation($value): string
    {
        $type = gettype($value);
        if (in_array($type, ['array', 'boolean', 'integer', 'double', 'string', 'NULL'])) {
            $rep = $value;
        } elseif ($type === 'object') {
            $rep = clone $value;
        } else {
            $rep = sprintf('<DATA:%s>', $type);
        }

        return $rep;
    }
}
