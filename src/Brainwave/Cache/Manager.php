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
 * @version     0.9.8-dev
 */

use Brainwave\Cache\Adapter\ApcCache;
use Brainwave\Cache\Adapter\ArrayCache;
use Brainwave\Cache\Adapter\FileCache;
use Brainwave\Cache\Adapter\MemcacheCache;
use Brainwave\Cache\Adapter\MemcachedCache;
use Brainwave\Cache\Adapter\NullCache;
use Brainwave\Cache\Adapter\RedisCache;
use Brainwave\Cache\Adapter\WinCacheCache;
use Brainwave\Cache\Adapter\XCacheCache;
use Brainwave\Cache\Exception\CacheException;
use Brainwave\Cache\Exception\InvalidArgumentException as InvalidCacheArgumentException;
use Brainwave\Contracts\Cache\Adapter as AdapterContract;
use Brainwave\Contracts\Cache\Factory as FactoryContract;
use Brainwave\Contracts\Config\Manager as ConfigContract;
use Brainwave\Filesystem\Filesystem;
use Brainwave\Support\Arr;
use Brainwave\Support\Manager;

/**
 * CacheManager.
 *
 * @author  Daniel Bannert
 *
 * @since   0.8.0-dev
 */
class CacheManager extends Manager implements FactoryContract
{
    /**
     * Config instance.
     *
     * @var \Brainwave\Contracts\Config\Manager
     */
    protected $config;

    /**
     * Filesystem instance.
     *
     * @var \Brainwave\Filesystem\Filesystem
     */
    protected $files;

    /**
     * The array of created "drivers".
     *
     * @var array
     */
    protected $drivers = [];

    /**
     * The registered custom driver creators.
     *
     * @var array
     */
    protected $customCreators = [];

    /**
     * Constructor.
     *
     * @param ConfigContract $config
     * @param Filesystem     $files
     * @param array          $supportedDrivers
     */
    public function __construct(ConfigContract $config, Filesystem $files, array $supportedDrivers = [])
    {
        $this->config           = $config;
        $this->files            = $files;
        $this->supportedDrivers = $supportedDrivers;
    }

    /**
     * Builder.
     *
     * @param string $driver  The cache driver to use
     * @param array  $options
     *
     * @throws CacheException
     *
     * @return mixed
     */
    public function driver($driver, array $options = [])
    {
        $class = parent::driver($driver, $options);

        if (!$class::isSupported()) {
            throw new CacheException(
                sprintf('The driver [%s] is not supported by your running settingsuration.', $driver)
            );
        }

        return $class;
    }

    /**
     * Get the cache "prefix" value.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->config->get('cache::prefix');
    }

    /**
     * Set the cache "prefix" value.
     *
     * @param string $name
     */
    public function setPrefix($name)
    {
        $this->config->bind('cache::prefix', $name);
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config->get('cache::driver');
    }

    /**
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
        $this->config->bind('cache::driver', $name);
    }

    /**
     * Set the cache "prefix" value.
     *
     * @param string $name
     */
    public function setPrefix($name)
    {
        $this->config->bind('cache::prefix', $name);
    }

    /**
     * Create an instance of the APC cache driver.
     *
     * @return Repository
     */
    protected function createApcDriver()
    {
        return $this->repository(new ApcCache($this->getPrefix()));
    }

    /**
     * Create an instance of the array cache driver.
     *
     * @return Repository
     */
    protected function createArrayDriver()
    {
        return $this->repository(new ArrayCache());
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @param array $config
     *
     * @return Repository
     */
    protected function createFileDriver(array $config = [])
    {
        $config = array_filter($config);

        $path = empty($config) ?
        $this->config->get('cache::path') :
        $config['path'];

        return $this->repository(new FileCache($this->files, $path));
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     * @param array $config
     *
     * @return Repository
     */
    protected function createMemcachedDriver(array $config = [])
    {
        $config = array_filter($config);

        $servers = empty($config) ?
        $this->config->get('cache::memcached') :
        $config['memcached'];

        $persistentConnectionId = Arr::get($config, 'persistent_id', false);
        $customOptions = Arr::get($config, 'options', []);
        $saslCredentials = array_filter(Arr::get($config, 'sasl', []));

        $memcached = MemcachedCache::connect(
            $servers,
            $persistentConnectionId,
            $customOptions,
            $saslCredentials
        );

        return $this->repository(new MemcachedCache($memcached, $this->getPrefix()));
    }

    /**
     * Create an instance of the Memcache cache driver.
     *
     * @param array $config
     *
     * @return Repository
     */
    protected function createMemcacheDriver(array $config = [])
    {
        $config = array_filter($config);

        $servers = empty($config) ?
        $this->config->get('cache::memcache') :
        $config['memcache'];

        $memcache = MemcacheCache::connect($servers);

        return $this->repository(new MemcacheCache($memcache, $this->getPrefix()));
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param array $config
     *
     * @return Repository
     */
    protected function createRedisDriver(array $config = [])
    {
        $settings = $this->config;

        $servers = $settings->get('cache::redis.parameters') !== null ? $settings->get('cache::redis.parameters') : $config['parameters'];
        $options = $settings->get('cache::redis.options') !== null ? $settings->get('cache::redis.options') : $config['options'];

        $redis = RedisCache::connect($servers, $options);

        return $this->repository(new RedisCache($redis, $this->getPrefix()));
    }

    /**
     * Create an instance of the Null cache driver.
     *
     * @return Repository
     */
    protected function createNullDriver()
    {
        return $this->repository(new NullCache());
    }

    /**
     * Create an instance of the WinCache cache driver.
     *
     * @return Repository
     */
    protected function createWincacheDriver()
    {
        return $this->repository(new WinCacheCache($this->getPrefix()));
    }

    /**
     * Create an instance of the XCache cache driver.
     *
     * @return Repository
     */
    protected function createXcacheDriver()
    {
        return $this->repository(new XCacheCache($this->getPrefix()));
    }

    /**
     * Create a new cache repository with the given implementation.
     *
     * @param AdapterContract $Cache
     *
     * @return \Brainwave\Cache\Repository
     */
    protected function repository(AdapterContract $Cache)
    {
        return new Repository($Cache);
    }
}
