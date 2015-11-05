<?php
namespace Brainwave\Cache;

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

use Brainwave\Contracts\Cache\Adapter as AdapterContract;
use Brainwave\Contracts\Cache\Repository as CacheContract;
use Carbon\Carbon;

/**
 * Repository.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class Repository implements CacheContract, \ArrayAccess
{
    /**
     * The cache driver implementation.
     *
     * @var \Brainwave\Contracts\Cache\Adapter
     */
    protected $driver;

    /**
     * The default number of minutes to driver items.
     *
     * @var int
     */
    protected $default = 60;

    /**
     * Cache driver supported.
     *
     * @var bool
     */
    protected static $supported = false;

    /**
     * Create a new cache repository instance.
     *
     * @param null|AdapterContract $driver
     */
    public function __construct(AdapterContract $driver = null)
    {
        $this->driver = $driver;

        self::$supported = $driver::isSupported();
    }

    /**
     * Check if the cache driver is supported.
     *
     * @return bool
     */
    public static function isSupported()
    {
        return static::$supported;
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
     * @param string      $key
     * @param string|null $default
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        $value = $this->driver->get($key);

        return ($value !== null) ? $value : value($default);
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        $value = $this->get($key, $default);

        $this->forget($key);

        return $value;
    }

    /**
     * Store an item in the cache.
     *
     * @param string        $key
     * @param string        $value
     * @param \DateTime|int $minutes
     */
    public function put($key, $value, $minutes)
    {
        $minutes = $this->getMinutes($minutes);

        if ($minutes !== null) {
            $this->driver->put($key, $value, $minutes);
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
            $this->put($key, $value, $minutes);

            return true;
        }

        return false;
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param string   $key
     * @param int      $minutes
     * @param \Closure $callback
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

        $this->put($key, $value = $callback(), $minutes);

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

        $this->remember($key, 0, $value = $callback());

        return $value;
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
        $this->driver->forget($key);
    }

    /**
     * Get the default cache time.
     *
     * @return int
     */
    public function getDefaultCacheTime()
    {
        return $this->default;
    }

    /**
     * Set the default cache time in minutes.
     *
     * @param int $minutes
     */
    public function setDefaultCacheTime($minutes)
    {
        $this->default = $minutes;
    }

    /**
     * Get the cache driver implementation.
     *
     * @return AdapterContract
     */
    public function getdriver()
    {
        return $this->driver;
    }

    /**
     * Determine if a cached value exists.
     *
     * @param string $key
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Store an item in the cache for the default time.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function offsetSet($key, $value)
    {
        $this->put($key, $value, $this->default);
    }

    /**
     * Remove an item from the cache.
     *
     * @param string $key
     *
     * @return bool|null
     */
    public function offsetUnset($key)
    {
        return $this->forget($key);
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

    /**
     * Pass missing methods to the store.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->driver, $method], $parameters);
    }
}
