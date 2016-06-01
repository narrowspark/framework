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
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem as Flysystem;
use Memcache;
use Memcached;
use MongoDB\Driver\Manager as MongoDBManager;
use Predis\Client as PredisClient;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Contracts\Config\Manager as ConfigContract;
use Viserio\Support\Manager;

class CacheManager extends Manager
{
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
        'local',
        'memcache',
        'memcached',
        'mongodb',
        'predis',
        'session',
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
     * Set the default cache driver name.
     *
     * @param string $name
     */
    public function setDefaultDriver(string $name)
    {
        $this->config->bind('cache::driver', $name);
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('cache::driver', '');
    }

    /**
     * Create an instance of the MongoDB cache driver.
     *
     * @param array $options
     *
     * @return MongoDBCachePool|null
     */
    public function createMongodbDriver(array $options)
    {
        $servers = empty($options) ?
            $this->config->get('cache::mongodb') :
            $options['mongodb'];

        if ($servers instanceof MongoDBManager) {
            return new MongoDBCachePool($servers);
        }
    }

    /**
     * Create an instance of the Predis cache driver.
     *
     * @param array $options
     *
     * @return PredisCachePool|null
     */
    public function createPredisDriver(array $options)
    {
        $servers = empty($options) ?
            $this->config->get('cache::predis') :
            $options['predis'];

        if ($servers instanceof PredisClient) {
            return new PredisCachePool($servers);
        }
    }

    /**
     * Create an instance of the Flysystem cache driver.
     *
     * @return FilesystemCachePool|null
     */
    protected function createFilesystemDriver(array $options)
    {
        $adapter = $this->config->get('cache::flysystem', $options['flysystem']);

        $filesystem = new Flysystem($adapter['connection']);

        return new FilesystemCachePool($filesystem);
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     *
     * @return MemcachedCachePool|null
     */
    protected function createMemcachedDriver(array $options)
    {
        $servers = empty($options) ?
            $this->config->get('cache::memcached') :
            $options['memcached'];

        if ($servers instanceof Memcached) {
            return new MemcachedCachePool($servers);
        }
    }

    /**
     * Create an instance of the Memcache cache driver.
     *
     * @param array $options
     *
     * @return MemcacheCachePool|null
     */
    protected function createMemcacheDriver(array $options)
    {
        $servers = empty($options) ?
            $this->config->get('cache::memcache') :
            $options['memcache'];

        if ($servers instanceof Memcache) {
            return new MemcacheCachePool($servers);
        }
    }

    /**
     * Create an instance of the local cache driver.
     *
     * @param array $options
     *
     * @return FilesystemCachePool|null
     */
    protected function createLocalDriver(array $options)
    {
        $adapter = empty($options) ?
            $this->config->get('cache::local') :
            $options['local'];

        if ($adapter instanceof Local) {
            return new FilesystemCachePool($adapter);
        }
    }

    /**
     * Create an instance of the session cache driver.
     *
     * @param array $options
     *
     * @return Psr6SessionHandler|null
     */
    protected function createSessionDriver(array $options)
    {
        $adapter = empty($options) ?
            $this->config->get('cache::session') :
            $options['session'];

        if (
            isset($options['local']['pool'], $options['local']['config']) &&
            $options['local']['pool'] instanceof CacheItemPoolInterface &&
            is_array($options['local']['config'])
        ) {
            return new Psr6SessionHandler($options['local']['pool'], $options['local']['config']);
        }
    }

    /**
     * Get the configuration name.
     *
     * @return string
     */
    protected function getConfigName(): string
    {
        return 'cache';
    }
}
