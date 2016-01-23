<?php
namespace Viserio\Cache;

use Cache\Adapter\Apc\ApcCachePool;
use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\Memcache\MemcacheCachePool;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Adapter\MongoDB\MongoDBCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Void\VoidCachePool;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem as Flysystem;
use Memcache;
use Memcached;
use MongoDB\Driver\Manager as MongoDBManager;
use Narrowspark\Arr\StaticArr as Arr;
use Predis\Client as PredisClient;
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
     * All supported drivers.
     *
     * @var array
     */
    protected $supportedDrivers = [
        'apc'         => ApcCachePool::class,
        'apcu'        => ApcuCachePool::class,
        'array'       => ArrayCachePool::class,
        'filesystem',
        'memcache',
        'memcached',
        'mongodb',
        'predis',
        'void'         => VoidCachePool::class,
    ];

    /**
     * Constructor.
     *
     * @param ConfigContract $config
     */
    public function __construct(ConfigContract $config)
    {
        $this->config = $config;
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
     * Create an instance of the Flysystem cache driver.
     *
     * @return \Psr\Cache\CacheItemPoolInterface|null
     */
    protected function createFilesystemDriver(array $options)
    {
        $adapter = empty($options) ?
            $this->config->get('cache::flysystem') :
            $options['flysystem'];

        if ($adapter instanceof AdapterInterface) {
            $filesystem = new Flysystem($options['connection']);

            return new FilesystemCachePool($filesystem);
        }

        return;
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     * @param array $config
     *
     * @return \Psr\Cache\CacheItemPoolInterface|null
     */
    protected function createMemcachedDriver(array $options)
    {
        $servers = empty($options) ?
            $this->config->get('cache::memcached') :
            $options['memcached'];

        if ($adapter instanceof Memcached) {
            return new MemcachedCachePool($memcached);
        }

        return;
    }

    /**
     * Create an instance of the Memcache cache driver.
     *
     * @param array $options
     *
     * @return \Psr\Cache\CacheItemPoolInterface|null
     */
    protected function createMemcacheDriver(array $options)
    {
        $servers = empty($options) ?
            $this->config->get('cache::memcache') :
            $options['memcache'];

        if ($adapter instanceof Memcache) {
            return new MemcacheCachePool($memcached);
        }

        return;
    }

    /**
     * Create an instance of the MongoDB cache driver.
     *
     * @param array $options
     *
     * @return \Psr\Cache\CacheItemPoolInterface|null
     */
    public function createMongodbDriver(array $options)
    {
        $servers = empty($options) ?
            $this->config->get('cache::mongodb') :
            $options['mongodb'];

        if ($adapter instanceof MongoDBManager) {
            return new MongoDBCachePool($memcached);
        }

        return;
    }

    /**
     * Create an instance of the Predis cache driver.
     *
     * @param array $options
     *
     * @return \Psr\Cache\CacheItemPoolInterface|null
     */
    public function createPredisDriver(array $options)
    {
        $servers = empty($options) ?
            $this->config->get('cache::predis') :
            $options['predis'];

        if ($adapter instanceof PredisClient) {
            return new PredisCachePool($memcached);
        }

        return;
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @param array $options
     *
     * @return \Psr\Cache\CacheItemPoolInterface|null
     */
    protected function createFileDriver(array $options)
    {
        $path = empty($options) ?
            $this->config->get('cache::file') :
            $options['file'];

        if (isset($options['file']['class'], $options['file']['path'])) {
            return new FileCachePool($options['file']['class'], $options['file']['path']);
        }

        return;
    }
}
