<?php
declare(strict_types=1);
namespace Viserio\Cache;

use Cache\Adapter\{
    Apc\ApcCachePool,
    Apcu\ApcuCachePool,
    Chain\CachePoolChain,
    Filesystem\FilesystemCachePool,
    Memcache\MemcacheCachePool,
    Memcached\MemcachedCachePool,
    MongoDB\MongoDBCachePool,
    PHPArray\ArrayCachePool,
    Predis\PredisCachePool,
    Redis\RedisCachePool,
    Void\VoidCachePool
};
use Cache\Namespaced\NamespacedCachePool;
use League\Flysystem\Filesystem as Flysystem;
use Memcache;
use Memcached;
use MongoDB\Driver\Manager as MongoDBManager;
use Predis\Client as PredisClient;
use Psr\Cache\CacheItemPoolInterface;
use Redis;
use Viserio\{
    Filesystem\FilesystemManager,
    Support\AbstractManager
};

class CacheManager extends AbstractManager
{
    /**
     *  Chain multiple PSR-6 Cache pools together for performance.
     *
     * @param array $pools
     *
     * @return \Cache\Adapter\Chain\CachePoolChain
     */
    public function chain(array $pools): CachePoolChain
    {
        return new CachePoolChain(
            $pools,
            (array) $this->config->get($this->getConfigName() . 'chain.options', [])
        );
    }

    /**
     * Create an instance of the Apc cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Apc\ApcCachePool
     */
    protected function createApcDriver(array $config): ApcCachePool
    {
        return new ApcCachePool();
    }

    /**
     * Create an instance of the Apcu cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Apcu\ApcuCachePool
     */
    protected function createApcuDriver(array $config): ApcuCachePool
    {
        return new ApcuCachePool();
    }

    /**
     * Create an instance of the Apcu cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\PHPArray\ArrayCachePool
     */
    protected function createArrayDriver(array $config): ArrayCachePool
    {
        return new ArrayCachePool();
    }

    /**
     * Create an instance of the MongoDB cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\MongoDB\MongoDBCachePool
     */
    protected function createMongodbDriver(array $config): MongoDBCachePool
    {
        if (isset($config['username'], $config['password'])) {
            $dns = sprintf(
                'mongodb://%s:%s@%s:%s',
                $config['username'],
                $config['password'],
                $config['server'],
                $config['port']
            );
        } else {
            $dns = sprintf('mongodb://%s:%s', $config['server'], $config['port']);
        }

        $collection = MongoDBCachePool::createCollection(
            new MongoDBManager($dns),
            $config['database'],
            $config['prefix']
        );

        return new MongoDBCachePool($collection);
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Redis\RedisCachePool
     */
    protected function createRedisDriver(array $config): RedisCachePool
    {
        $client = new Redis();
        $client->connect($config['host'], $config['port']);

        return new RedisCachePool($client);
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
        $client = new PredisClient(sprintf('tcp:/%s:%s', $config['server'], $config['port']));

        return new PredisCachePool($servers);
    }

    /**
     * Create an instance of the Flysystem cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Filesystem\FilesystemCachePool
     */
    protected function createFilesystemDriver(array $config): FilesystemCachePool
    {
        $adapter = new FilesystemManager($this->config);

        $filesystem = new Flysystem($adapter->connection($config['connection']));

        return new FilesystemCachePool($filesystem);
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     *
     * @return \Cache\Adapter\Memcached\MemcachedCachePool
     */
    protected function createMemcachedDriver(array $config): MemcachedCachePool
    {
        $client = new Memcached();
        $client->addServer($config['host'], $config['port']);

        return new MemcachedCachePool($client);
    }

    /**
     * Create an instance of the Memcache cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Memcache\MemcacheCachePool|null
     */
    protected function createMemcacheDriver(array $config): MemcacheCachePool
    {
        $client = new Memcache();
        $client->addServer($config['host'], $config['port']);

        return new MemcacheCachePool($client);
    }

    /**
     * Create an instance of the Void cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Void\VoidCachePool
     */
    protected function createNullDriver(array $config): VoidCachePool
    {
        return new VoidCachePool();
    }

    /**
     * Create an instance of the session cache driver.
     *
     * @param array $config
     *
     * @return \Cache\SessionHandler\Psr6SessionHandler
     */
    protected function createSessionDriver(array $config): Psr6SessionHandler
    {
        $pool = $this->driver($config['session']['pool']);

        return new Psr6SessionHandler($pool, $config['session']['config']);
    }

    /**
     * Create a prefixed cache pool with a namespace.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $hierarchyPool
     *
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    protected function namespacedPool(CacheItemPoolInterface $hierarchyPool)
    {
        $namespace = $this->config->get($this->getConfigName() . 'namespace', 'viserio');

        return new NamespacedCachePool($hierarchyPool, $namespace);
    }

    /**
     * {@inheritdoc}
     */
    protected function createDriver(array $config)
    {
        $driver = parent::createDriver($config);

        return $this->namespacedPool($driver);
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
