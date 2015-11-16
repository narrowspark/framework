<?php
namespace Viserio\Cache\Adapter;

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

use Viserio\Cache\Store\TaggableStore;
use Viserio\Contracts\Cache\Adapter as AdapterContract;

/**
 * ApcCache.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class ApcCache extends TaggableStore implements AdapterContract
{
    /**
     * Indicates if APCu is supported.
     *
     * @var bool
     */
    protected $apcu = false;

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
     * Create a new APC store.
     *
     * @param string $prefix
     */
    public function __construct($prefix = '')
    {
        $this->apcu = function_exists('apcu_fetch');
        $this->prefix = $prefix;
    }

    /**
     * Check if the cache driver is supported.
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported()
    {
        return extension_loaded('apc') ? extension_loaded('apc') : function_exists('apcu_fetch');
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
        $value = $this->apcu ?
        apcu_fetch($this->prefix.$key) :
        apc_fetch($this->prefix.$key);

        if ($value !== false) {
            return $value;
        }

        return;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     */
    public function put($key, $value, $minutes)
    {
        $this->minutes[$key] = $minutes;

        $this->apcu ?
        apcu_store($this->prefix.$key, $value, $minutes * 60) :
        apc_store($this->prefix.$key, $value, $minutes * 60);
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
        return $this->apcu ?
        apcu_inc($this->prefix.$key, $value) :
        apc_inc($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     *
     * @return int|bool
     */
    public function decrement($key, $value = 1)
    {
        return $this->apcu ?
        apcu_dec($this->prefix.$key, $value) :
        apc_dec($this->prefix.$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     *
     * @return array|bool|null
     */
    public function forever($key, $value)
    {
        $this->put($key, $value, 0);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return array|bool
     */
    public function forget($key)
    {
        return $this->apcu ?
        apcu_delete($this->prefix.$key) :
        apc_delete($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush()
    {
        $this->apcu ? apcu_clear_cache() : apc_clear_cache('user');
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
