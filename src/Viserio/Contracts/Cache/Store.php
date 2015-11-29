<?php
namespace Viserio\Contracts\Cache;

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
 * @version     0.10.0
 */

/**
 * Store.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
interface Store
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get($key);

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $minutes
     */
    public function put($key, $value, $minutes);

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     */
    public function increment($key, $value = 1);

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     */
    public function decrement($key, $value = 1);

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever($key, $value);

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function forget($key);

    /**
     * Remove all items from the cache.
     */
    public function flush();

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix();
}
