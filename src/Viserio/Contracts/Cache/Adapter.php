<?php
namespace Viserio\Contracts\Cache;

interface Adapter
{
    /**
     * Check if the cache driver is supported.
     *
     * @return bool Returns TRUE if supported or FALSE if not.
     */
    public static function isSupported();

    /**
     * Invalidate all items in the cache.
     *
     * @return bool Returns TRUE on success or FALSE on failure.
     */
    public function flush();

    /**
     * Fetch a stored variable from the cache.
     *
     * @param string $key The key used to store the value
     *
     * @return mixed The stored variable
     */
    public function get($key);

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
     */
    public function forget($key);

    /**
     * Store variable in the cache.
     *
     * @param string $key     The key to use to put the value
     * @param mixed  $value   The variable to put
     * @param int    $minutes
     *
     * @return null|bool
     */
    public function put($key, $value, $minutes);

    /**
     * Store multiple items in the cache for a set number of minutes.
     *
     * @param array $values  array of key => value pairs
     * @param int   $minutes
     */
    public function putMultiple(array $values, $minutes);

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
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix();
}
