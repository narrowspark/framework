<?php
namespace Viserio\Cache\Adapter;

use Memcached;
use Viserio\Cache\Store\TaggableStore;
use Viserio\Contracts\Cache\Adapter as AdapterContract;

class MemcachedCache extends TaggableStore implements AdapterContract
{
    /**
     * The Memcached instance.
     *
     * @var \Memcached
     */
    protected $memcached;

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
        return extension_loaded('memcached');
    }

    /**
     * Create a new Memcached connection.
     *
     * @param array             $servers
     * @param bool|false|string $persistentConnectionId
     * @param array             $customOptions
     * @param array             $saslCredentials
     *
     * @throws RuntimeException
     *
     * @return \Memcached
     */
    public static function connect(
        array $servers,
        $persistentConnectionId = false,
        array $customOptions = [],
        array $saslCredentials = []
    ) {
        $memcached = static::getMemcached($persistentConnectionId);

       // Validate and set custom options
        if (count($customOptions)) {
            $memcachedConstants = array_map(
                function ($option) {
                    $constant = sprintf('Memcached::%s', $option);
                    if (!defined($constant)) {
                        throw new RuntimeException(sprintf('Invalid Memcached option: [%s]', $constant));
                    }

                    return constant($constant);
                },
                array_keys($customOptions)
            );

            $memcached->setOption(array_combine($memcachedConstants, $customOptions));
        }

        // Set SASL auth data, requires binary protocol
        if (count($saslCredentials) === 2) {
            list($username, $password) = $saslCredentials;
            $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
            $memcached->setSaslAuthData($username, $password);
        }

        // Only add servers if we need to. If using a persistent connection
        // the servers must only be added the first time otherwise connections
        // are duplicated.
        if (!$memcached->getServerList()) {
            foreach ($servers as $server) {
                $memcached->addServer(
                    $server['host'],
                    $server['port'],
                    $server['weight']
                );
            }
        }

        // Verify connection
        $memcachedStatus = $memcached->getVersion();

        if (!is_array($memcachedStatus)) {
            throw new RuntimeException('No Memcached servers added.');
        }

        if (in_array('255.255.255', $memcachedStatus, true) && count(array_unique($memcachedStatus)) === 1) {
            throw new RuntimeException('Could not establish Memcached connection.');
        }

        return $memcached;
    }

    /**
     * Get a new Memcached instance.
     *
     * @param bool|string $persistentConnectionId
     *
     * @return \Memcached
     */
    protected static function getMemcached($persistentConnectionId)
    {
        if (false !== $persistentConnectionId) {
            return new Memcached($persistentConnectionId);
        }

        return new Memcached();
    }

    /**
     * Create a new Memcached store.
     *
     * @param \Memcached $memcached
     * @param string     $prefix
     */
    public function __construct($memcached, $prefix = '')
    {
        $this->memcached = $memcached;
        $this->prefix = strlen($prefix) > 0 ? $prefix . ':' : '';
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
        $value = $this->memcached->get($this->prefix . $key);

        if ($this->memcached->getResultCode() === 0) {
            return $value;
        }

        return;
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value for the key.
     *
     * @param array $keys
     *
     * @return array
     */
    public function getMultiple(array $keys)
    {
        $prefixedKeys = [];

        foreach ($keys as $keyToPrefix) {
            $prefixedKeys[] = $this->prefix . $keyToPrefix;
        }

        $cas = null;
        $cacheValues = $this->memcached->getMulti($prefixedKeys, $cas, Memcached::GET_PRESERVE_ORDER);

        if ($this->memcached->getResultCode() !== 0) {
            return array_fill_keys($keys, null);
        }

        $returnValues = array_combine($keys, $cacheValues);

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

        $this->memcached->set($this->prefix . $key, $value, $minutes * 60);
    }

    /**
     * Store multiple items in the cache for a set number of minutes.
     *
     * @param array $values
     * @param int   $minutes
     */
    public function putMultiple(array $values, $minutes)
    {
        $formattedKeyValues = [];

        foreach ($values as $keyToPrefix => $singleValue) {
            $formattedKeyValues[$this->prefix . $keyToPrefix] = $singleValue;
        }

        $this->memcached->setMulti($formattedKeyValues, $minutes * 60);
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
        return $this->memcached->increment($this->prefix . $key, $value);
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
        return $this->memcached->decrement($this->prefix . $key, $value);
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
        $this->memcached->delete($this->prefix . $key);
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool|null
     */
    public function flush()
    {
        $this->memcached->flush();
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
