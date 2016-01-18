<?php
namespace Viserio\Cache\Adapter;

use Viserio\Cache\Adapter\Traits\MultipleTrait;
use Viserio\Cache\Store\TaggableStore;
use Viserio\Contracts\Cache\Adapter as AdapterContract;

class WinCacheCache extends TaggableStore implements AdapterContract
{
    use MultipleTrait;

    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Time of a stored item.
     *
     * @var array
     */
    protected $minutes = [];

    /**
     * Check if the cache driver is supported.
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported()
    {
        return extension_loaded('wincache');
    }

    /**
     * Create a new WinCache store.
     *
     * @param string $prefix
     */
    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key)
    {
        $value = wincache_ucache_get($this->prefix . $key);

        if ($value !== false) {
            return $value;
        }

        return;
    }

    /**
     * Retrieve multiple items from the cache by key,
     * items not found in the cache will have a null value for the key.
     *
     * @param string[] $keys
     *
     * @return array
     */
    public function getMultiple(array $keys)
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
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     *
     * @return bool|null
     */
    public function put($key, $value, $minutes)
    {
        $this->minutes[$key] = $minutes;

        wincache_ucache_set($this->prefix . $key, $value, $minutes * 60);
    }

    /**
     * Store multiple items in the cache for a set number of minutes.
     *
     * @param array $values  array of key => value pairs
     * @param int   $minutes
     */
    public function putMultiple(array $values, $minutes)
    {
        foreach ($values as $key => $singleValue) {
            $this->put($key, $singleValue, $minutes);
        }
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     *
     * @return int|bool
     */
    public function increment($key, $value = 1)
    {
        return wincache_ucache_inc($this->prefix . $key, $value);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     *
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return wincache_ucache_dec($this->prefix . $key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return bool|null
     */
    public function forever($key, $value)
    {
        return $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     */
    public function forget($key)
    {
        wincache_ucache_delete($this->prefix . $key);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush()
    {
        wincache_ucache_clear();
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Get the stored time of a item.
     *
     * @param string $key
     *
     * @return int
     */
    public function getStoredItemTime($key)
    {
        return $this->minutes[$key];
    }
}
