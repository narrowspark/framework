<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\DataCollectors\Bridge\Recording;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\WebProfiler\Util\TemplateHelper;
use StdClass;

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
     * RecordingAdapter constructor.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cachePool
     */
    public function __construct(CacheItemPoolInterface $cachePool)
    {
        $this->cachePool = $cachePool;
    }

    /**
     * {@inheritdoc}
     */
    public function getItem($key): CacheItemInterface
    {
        $call = $this->timeCall(__FUNCTION__, array($key));
        $result = $call->result;
        $call->isHit = $result->isHit();

        // Display the result in a good way depending on the data type
        if ($call->isHit) {
            $call->result = TemplateHelper::dump($result->get());
        } else {
            $call->result = null;
        }

        $this->addCall($call);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key): bool
    {
        $call = $this->timeCall(__FUNCTION__, array($key));
        $this->addCall($call);

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key): bool
    {
        $call = $this->timeCall(__FUNCTION__, array($key));
        $this->addCall($call);

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        $key = $item->getKey();
        $call = $this->timeCall(__FUNCTION__, array($item));
        $call->arguments = ['<CacheItem>', $key, TemplateHelper::dump($item->get())];
        $this->addCall($call);

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $key = $item->getKey();
        $call = $this->timeCall(__FUNCTION__, array($item));
        $call->arguments = array('<CacheItem>', $key, TemplateHelper::dump($item->get()));
        $this->addCall($call);

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(array $keys = [])
    {
        $call = $this->timeCall(__FUNCTION__, [$keys]);
        $result = $call->result;
        $call->result = sprintf('<DATA:%s>', gettype($result));
        $this->addCall($call);

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $call = $this->timeCall(__FUNCTION__, []);
        $this->addCall($call);

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        $call = $this->timeCall(__FUNCTION__, [$keys]);
        $this->addCall($call);

        return $call->result;
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        $call = $this->timeCall(__FUNCTION__);
        $this->addCall($call);

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
     * Record a call.
     *
     * @param \StdClass $call
     */
    private function addCall(StdClass $call)
    {
        $this->calls[] = $call;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return \StdClass
     */
    private function timeCall(string $name, array $arguments = []): StdClass
    {
        $start = microtime(true);
        $result = call_user_func_array(array($this->cachePool, $name), $arguments);
        $time = microtime(true) - $start;

        $object = (object) compact('name', 'arguments', 'start', 'time', 'result');

        return $object;
    }
}
