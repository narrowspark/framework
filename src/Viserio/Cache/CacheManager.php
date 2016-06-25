<?php
namespace Viserio\Cache;

use Cache\Adapter\{
    Apc\ApcCachePool,
    Apcu\ApcuCachePool,
    Filesystem\FilesystemCachePool,
    Memcache\MemcacheCachePool,
    Memcached\MemcachedCachePool,
    MongoDB\MongoDBCachePool,
    PHPArray\ArrayCachePool,
    Predis\PredisCachePool,
    Redis\RedisCachePool,
    Void\VoidCachePool
};
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem as Flysystem;
use Memcache;
use Memcached;
use MongoDB\Driver\Manager as MongoDBManager;
use Predis\Client as PredisClient;
use Psr\Cache\CacheItemPoolInterface;
use Redis;
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
        'redis',
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
        $this->config->set($this->getConfigName() . '::driver', $name);
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get($this->getConfigName() . '::driver', '');
    }

    /**
     * Create an instance of the MongoDB cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\MongoDB\MongoDBCachePool|null
     */
    protected function createMongodbDriver(array $config)
    {
        $servers = $this->config->get($this->getConfigName() . '::mongodb', $config);

        if ($servers instanceof MongoDBManager) {
            return new MongoDBCachePool($servers);
        }
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Redis\RedisCachePool|null
     */
    protected function createRedisDriver(array $config)
    {
        $servers = $this->config->get($this->getConfigName() . '::redis', $config);

        if ($servers instanceof Redis) {
            return new RedisCachePool($servers);
        }
    }

    /**
     * Create an instance of the Predis cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Predis\PredisCachePool|null
     */
    protected function createPredisDriver(array $config)
    {
        $servers = $this->config->get($this->getConfigName() . '::predis', $config);

        if ($servers instanceof PredisClient) {
            return new PredisCachePool($servers);
        }
    }

    /**
     * Create an instance of the Flysystem cache driver.
     *
     * @param array $config
     *
     * @return FilesystemCachePool|null
     */
    protected function createFilesystemDriver(array $config)
    {
        $adapter = $this->config->get($this->getConfigName() . '::flysystem', $config);

        $filesystem = new Flysystem($adapter['connection']);

        return new FilesystemCachePool($filesystem);
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     *
     * @return \Cache\Adapter\Memcached\MemcachedCachePool|null
     */
    protected function createMemcachedDriver(array $config)
    {
        $servers = $this->config->get($this->getConfigName() . '::memcached', $config);

        if ($servers instanceof Memcached) {
            return new MemcachedCachePool($servers);
        }
    }

    /**
     * Create an instance of the Memcache cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Memcache\MemcacheCachePool|null
     */
    protected function createMemcacheDriver(array $config)
    {
        $servers = $this->config->get($this->getConfigName() . '::memcache', $config);

        if ($servers instanceof Memcache) {
            return new MemcacheCachePool($servers);
        }
    }

    /**
     * Create an instance of the local cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Filesystem\FilesystemCachePool|null
     */
    protected function createLocalDriver(array $config)
    {
        $adapter = $this->config->get($this->getConfigName() . '::local', $config);

        if ($adapter instanceof Local) {
            return new FilesystemCachePool($adapter);
        }
    }

    /**
     * Create an instance of the session cache driver.
     *
     * @param array $config
     *
     * @return Psr6SessionHandler|null
     */
    protected function createSessionDriver(array $config)
    {
        $adapter = $this->config->get($this->getConfigName() . '::session', $config);

        if (
            isset($config['local']['pool'], $config['local']['config']) &&
            $config['local']['pool'] instanceof CacheItemPoolInterface &&
            is_array($config['local']['config'])
        ) {
            return new Psr6SessionHandler($config['local']['pool'], $config['local']['config']);
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
