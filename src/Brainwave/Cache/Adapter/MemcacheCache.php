<?php
namespace Brainwave\Cache\Adapter;

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

use Brainwave\Cache\Store\TaggableStore;
use Brainwave\Contracts\Cache\Adapter as AdapterContract;

/**
 * MemcacheCache.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.2-dev
 */
class MemcacheCache extends TaggableStore implements AdapterContract
{
    /**
     * The Memcache instance.
     *
     * @var \Memcache
     */
    private $memcache;

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
        return extension_loaded('memcache');
    }

    /**
     * Create a new Memcache connection.
     *
     * @param array $servers
     *
     * @throws \RuntimeException
     *
     * @return \Memcache
     */
    public static function connect(array $servers)
    {
        $memcached = static::getMemcache();

        // For each server in the array, we'll just extract the configuration and add
        // the server to the Memcache connection. Once we have added all of these
        // servers we'll verify the connection is successful and return it back.
        foreach ($servers as $server) {
            $memcached->addServer(
                $server['host'],
                $server['port'],
                $server['weight']
            );
        }

        if (in_array('255.255.255', $memcached->getVersion(), true)) {
            throw new \RuntimeException('Could not establish connection to one or more Memcache servers.');
        }

        return $memcached;
    }

    /**
     * Get a new Memcache instance.
     *
     * @return \Memcache
     */
    protected static function getMemcache()
    {
        return new \Memcache();
    }

    /**
     * Create a new file cache store instance.
     *
     * @param \Memcache $memcache
     * @param string    $prefix
     */
    public function __construct(\Memcache $memcache, $prefix = '')
    {
        $this->memcache = $memcache;
        $this->prefix = strlen($prefix) > 0 ? $prefix.':' : '';
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
        return $this->memcache->get($this->prefix.$key);
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

        $this->memcache->set($this->prefix.$key, $value, $minutes * 60);
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     *
     * @return int
     */
    public function increment($key, $value = 1)
    {
        return $this->memcache->increment($this->prefix.$key, $value);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param string $key
     * @param int    $value
     *
     * @return int
     */
    public function decrement($key, $value = 1)
    {
        return $this->memcache->decrement($this->prefix.$key, $value);
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
     *
     * @return bool
     */
    public function forget($key)
    {
        return $this->memcache->delete($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        return $this->memcache->flush();
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
