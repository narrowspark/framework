<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\DataCollector\Bridge\Cache\Traits;

use Traversable;

trait SimpleTraceableCacheDecoratorTrait
{
    /**
     * {@inheritdoc}
     */
    public function get($key, $default = null)
    {
        $miss  = null !== $default && \is_object($default) ? $default : $this->miss;
        $event = $this->start(__FUNCTION__);

        try {
            $value = $this->pool->get($key, $miss);
        } finally {
            $event->end = \microtime(true);
        }

        if ($event->result[$key] = $miss !== $value) {
            ++$event->hits;
        } else {
            ++$event->misses;
            $value = $default;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has($key)
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result[$key] = $this->pool->has($key);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete($key)
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result[$key] = $this->pool->delete($key);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value, $ttl = null)
    {
        $event = $this->start(__FUNCTION__);

        try {
            return $event->result[$key] = $this->pool->set($key, $value, $ttl);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple($values, $ttl = null)
    {
        $event                 = $this->start(__FUNCTION__);
        $event->result['keys'] = [];

        if ($values instanceof Traversable) {
            $values = function () use ($values, $event) {
                foreach ($values as $k => $v) {
                    $event->result['keys'][] = $k;

                    yield $k => $v;
                }
            };
            $values = $values();
        } elseif (\is_array($values)) {
            $event->result['keys'] = \array_keys($values);
        }

        try {
            return $event->result['result'] = $this->pool->setMultiple($values, $ttl);
        } finally {
            $event->end = \microtime(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple($keys, $default = null)
    {
        $miss  = null !== $default && \is_object($default) ? $default : $this->miss;
        $event = $this->start(__FUNCTION__);

        try {
            $result = $this->pool->getMultiple($keys, $miss);
        } finally {
            $event->end = \microtime(true);
        }

        $f = function () use ($result, $event, $miss, $default) {
            $event->result = [];
            foreach ($result as $key => $value) {
                if ($event->result[$key] = $miss !== $value) {
                    ++$event->hits;
                } else {
                    ++$event->misses;
                    $value = $default;
                }
                yield $key => $value;
            }
        };

        return $f();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple($keys)
    {
        $event = $this->start(__FUNCTION__);

        if ($keys instanceof Traversable) {
            $keys = $event->result['keys'] = \iterator_to_array($keys, false);
        } else {
            $event->result['keys'] = $keys;
        }

        try {
            return $event->result['result'] = $this->pool->deleteMultiple($keys);
        } finally {
            $event->end = \microtime(true);
        }
    }
}
