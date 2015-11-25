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
 * NullCache.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class NullCache extends TaggableStore implements AdapterContract
{
    /**
     * The array of stored values.
     *
     * @var array
     */
    protected $storage = [];

    /**
     * Check if the cache driver is supported.
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported()
    {
        return true;
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
        //
    }

    /**
     * Retrieve multiple items from the cache by key,
     * items not found in the cache will have a null value for the key.
     *
     * @param string[] $keys
     *
     * @return array
     */
    public function getMulti(array $keys)
    {
        //
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string $key
     * @param string $value
     * @param int    $minutes
     */
    public function put($key, $value, $minutes)
    {
        //
    }

    /**
     * Store multiple items in the cache for a set number of minutes.
     *
     * @param array $values array of key => value pairs
     * @param int   $minutes
     *
     * @return void
     */
    public function putMulti(array $values, $minutes)
    {
        //
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     *
     * @return int|null
     */
    public function increment($key, $value = 1)
    {
        //
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     *
     * @return int|null
     */
    public function decrement($key, $value = 1)
    {
        //
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever($key, $value)
    {
        //
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     */
    public function forget($key)
    {
        //
    }

    /**
     * Remove all items from the cache.
     */
    public function flush()
    {
        //
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return '';
    }

    /**
     * Get the stored time of a item.
     *
     * @param string $key
     *
     * @return int|null
     */
    public function getStoredItemTime($key)
    {
        //
    }
}
