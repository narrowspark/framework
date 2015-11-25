<?php
namespace Viserio\Cache\Store;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Carbon\Carbon;
use Viserio\Contracts\Cache\Adapter;
use Viserio\Contracts\Cache\Store as StoreContract;
use Viserio\Support\Helper;

/**
 * TaggedCache.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class TaggedCache implements StoreContract
{
    /**
     * The cache store implementation.
     *
     * @var \Viserio\Contracts\Cache\Adapter
     */
    protected $store;

    /**
     * The tag set instance.
     *
     * @var \Viserio\Cache\Store\TagSet
     */
    protected $tags;

    /**
     * Create a new tagged cache instance.
     *
     * @param \Viserio\Cache\Store\TaggableStore $store
     * @param \Viserio\Cache\Store\TagSet        $tags
     *
     * @throws \Exception
     */
    public function __construct($store, TagSet $tags)
    {
        $this->tags = $tags;

        if ($store instanceof Adapter) {
            $this->store = $store;
        } else {
            throw new \Exception(sprintf('%s is not a instance of "\Viserio\Contracts\Cache\Adapter"', $store));
        }
    }

    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key)
    {
        return $this->get($key) !== null;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->store->get($this->taggedItemKey($key));

        return ($value !== null) ? $value : Helper::value($default);
    }

    /**
     * Retrieve multiple items from the cache by key,
     * items not found in the cache will have a null value for the key.
     *
     * @param string[] $keys
     * @return array
     */
    public function getMulti(array $keys)
    {
        $returnValues = [];

        foreach ($keys as $singleKey) {
            $returnValues[$singleKey] = $this->get($singleKey);
        }

        return $returnValues;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string        $key
     * @param mixed         $value
     * @param \DateTime|int $minutes
     */
    public function set($key, $value, $minutes)
    {
        $minutes = $this->getMinutes($minutes);

        if ($minutes !== null) {
            $this->store->put($this->taggedItemKey($key), $value, $minutes);
        }
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string        $key
     * @param mixed         $value
     * @param \DateTime|int $minutes
     */
    public function put($key, $value, $minutes)
    {
        $minutes = $this->getMinutes($minutes);

        if ($minutes !== null) {
            $this->store->put($this->taggedItemKey($key), $value, $minutes);
        }
    }

    /**
     * Store multiple items in the cache for a set number of minutes.
     *
     * @param array $values array of key => value pairs
     * @param int   $minutes
     * @return void
     */
    public function putMulti(array $values, $minutes)
    {
        foreach ($values as $key => $singleValue) {
            $this->put($key, $singleValue, $minutes);
        }
    }

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param string        $key
     * @param mixed         $value
     * @param \DateTime|int $minutes
     *
     * @return bool
     */
    public function add($key, $value, $minutes)
    {
        if ($this->get($key) === null) {
            $this->set($key, $value, $minutes);

            return true;
        }

        return false;
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     */
    public function increment($key, $value = 1)
    {
        $this->store->increment($this->taggedItemKey($key), $value);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     */
    public function decrement($key, $value = 1)
    {
        $this->store->decrement($this->taggedItemKey($key), $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever($key, $value)
    {
        $this->store->forever($this->taggedItemKey($key), $value);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool|null
     */
    public function forget($key)
    {
        $this->store->forget($this->taggedItemKey($key));
    }

    /**
     * Remove all items from the cache.
     */
    public function flush()
    {
        $this->tags->reset();
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string        $key
     * @param \DateTime|int $minutes
     * @param \Closure      $callback
     *
     * @return mixed
     */
    public function remember($key, $minutes, \Closure $callback)
    {
        // If the item exists in the cache we will just return this immediately
        // otherwise we will execute the given Closure and cache the result
        // of that execution for the given number of minutes in storage.
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $this->set($key, $value = $callback(), $minutes);

        return $value;
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string   $key
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function rememberForever($key, \Closure $callback)
    {
        // If the item exists in the cache we will just return this immediately
        // otherwise we will execute the given Closure and cache the result
        // of that execution for the given number of minutes. It's easy.
        $value = $this->get($key);
        if ($value !== null) {
            return $value;
        }

        $this->forever($key, $value = $callback());

        return $value;
    }

    /**
     * Get a fully qualified key for a tagged item.
     *
     * @param string $key
     *
     * @return string
     */
    public function taggedItemKey($key)
    {
        return sha1($this->tags->getNamespace()).':'.$key;
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->store->getPrefix();
    }

    /**
     * Calculate the number of minutes with the given duration.
     *
     * @param \DateTime|int $duration
     *
     * @return int|null
     */
    protected function getMinutes($duration)
    {
        if ($duration instanceof \DateTime) {
            $fromNow = Carbon::instance($duration)->diffInMinutes();

            return $fromNow > 0 ? $fromNow : null;
        }

        return is_string($duration) ? (int) $duration : $duration;
    }
}
