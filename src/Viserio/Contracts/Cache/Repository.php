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
 * @version     0.10.0-dev
 */

/**
 * Repository.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Repository
{
    /**
     * Retrieve an item from the cache by key.
     *
     * @param string      $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function get($key, $default = null);

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool|null
     */
    public function forget($key);

    /**
     * Store variable in the cache.
     *
     * @param string $key     The key to use to set the value
     * @param string $value   The variable to set
     * @param int    $minutes
     */
    public function put($key, $value, $minutes);

    /**
     * Determine if an item exists in the cache.
     *
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function pull($key, $default = null);

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string   $key
     * @param int      $minutes
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function remember($key, $minutes, \Closure $callback);

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param string   $key
     * @param \Closure $callback
     *
     * @return mixed
     */
    public function rememberForever($key, \Closure $callback);

    /**
     * Check if the cache driver is supported.
     *
     * @return bool Returns TRUE if supported or false if not.
     */
    public static function isSupported();
}
