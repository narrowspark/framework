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
use Predis\Client as Client;
use Predis\Connection\ConnectionException;

/**
 * RedisCache.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class RedisCache extends TaggableStore implements AdapterContract
{
    /**
     * A string that should be prepended to keys.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Predis Client instance.
     *
     * @var \Predis\Client
     */
    protected $redis;

    /**
     * The Redis connection that should be used.
     *
     * @var string
     */
    protected $connection;

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
        return extension_loaded('redis');
    }

    /**
     * Create a new Redis connection.
     *
     * @param string $parameters
     * @param array  $options
     *
     * @return \Predis\Client
     */
    public static function connect($parameters, array $options)
    {
        // since we connect to default setting localhost
        // and 6379 port there is no need for extra
        // configuration. If not then you can specify the
        // scheme, host and port to connect as an array
        // to the constructor.
        $client = static::getRedis($parameters, $options);

        try {
            $client->connect();
        } catch (ConnectionException $exception) {
            throw new \RuntimeException("Couldn't connected to Redis: ".$exception->getMessage());
        }

        return $client;
    }

    /**
     * Get a new Predis instance.
     *
     * @param string $parameters
     * @param array  $options
     *
     * @return \Predis\Client
     */
    protected static function getRedis($parameters = '', array $options = [])
    {
        $options = array_filter($options);

        if (!empty($parameters)) {
            $redis = new Client($parameters);
        } elseif (!empty($parameters) && !empty($options)) {
            $redis = new Client($parameters, $options);
        } else {
            $redis = new Client();
        }

        return new $redis();
    }

    /**
     * Create a new RedisCache store.
     *
     * @param Client $redis
     * @param string $prefix
     * @param string $connection
     */
    public function __construct(Client $redis, $prefix = '', $connection = 'default')
    {
        $this->redis = $redis;
        $this->connection = $connection;
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
        $value = $this->connection()->get($this->prefix.$key);
        if ($value !== null) {
            return is_numeric($value) ? $value : unserialize($value);
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

        $value = is_numeric($value) ? $value : serialize($value);

        $this->connection()->setex($this->prefix.$key, $minutes * 60, $value);
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
        return $this->connection()->incrby($this->prefix.$key, $value);
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
        return $this->connection()->decrby($this->prefix.$key, $value);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function forever($key, $value)
    {
        $value = is_numeric($value) ? $value : serialize($value);

        $this->connection()->put($this->prefix.$key, $value);
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
        return (bool) $this->connection()->del($this->prefix.$key);
    }

    /**
     * Remove all items from the cache.
     */
    public function flush()
    {
        $this->connection()->flushdb();
    }

    /**
     * Begin executing a new tags operation.
     *
     * @param array|mixed $names
     *
     * @return \Brainwave\Cache\Adapter\RedisTaggedCache
     */
    public function tags($names)
    {
        return new RedisTaggedCache($this, new TagSet($this, is_array($names) ? $names : func_get_args()));
    }

    /**
     * Get the Redis connection instance.
     *
     * @return \Predis\ClientInterface
     */
    public function connection()
    {
        return $this->redis->connection($this->connection);
    }

    /**
     * Set the connection name to be used.
     *
     * @param string $connection
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;
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
