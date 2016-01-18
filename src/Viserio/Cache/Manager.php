<?php
namespace Viserio\Cache;

use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Cache\Exception\CacheException;
use Viserio\Contracts\Cache\Adapter as AdapterContract;
use Viserio\Contracts\Cache\Factory as FactoryContract;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Filesystem\Filesystem;
use Viserio\Support\Manager;

class CacheManager extends Manager implements FactoryContract
{
    /**
     * Config instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;

    /**
     * Filesystem instance.
     *
     * @var \Viserio\Filesystem\Filesystem
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
     * Get the cache "prefix" value.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->config->get('cache::prefix');
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

        return $this->repository(new RedisCache($redis, $this->getPrefix()));
    }
}
