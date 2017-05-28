<?php
declare(strict_types=1);
namespace Viserio\Component\Cache;

use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\Memcache\MemcacheCachePool;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Adapter\MongoDB\MongoDBCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\Hierarchy\HierarchicalPoolInterface;
use Cache\Namespaced\NamespacedCachePool;
use Defuse\Crypto\Key;
use League\Flysystem\Filesystem as Flysystem;
use Memcache;
use Memcached;
use MongoDB\Driver\Manager as MongoDBManager;
use Predis\Client as PredisClient;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerAwareInterface;
use Redis;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Component\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Support\AbstractManager;

class CacheManager extends AbstractManager implements CacheManagerContract, LoggerAwareInterface, ProvidesDefaultOptionsContract
{
    use LoggerAwareTrait;

    /**
     * Create a new cache manager instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'default'   => 'array',
            'namespace' => false,
            'key'       => false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createDriver(array $config)
    {
        $driver    = parent::createDriver($config);
        $namespace = $this->options['namespace'];

        if (class_exists(NamespacedCachePool::class) && $namespace && $driver instanceof HierarchicalPoolInterface) {
            $driver = $this->getNamespacedPool($driver, $namespace);
        }

        if ($this->logger !== null) {
            $driver->setLogger($this->getLogger());
        }

        return $driver;
    }

    /**
     * Create an instance of the Array cache driver.
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
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
     * @return \Cache\Adapter\Predis\PredisCachePool
     *
     * @codeCoverageIgnore
     */
    protected function createPredisDriver(array $config): PredisCachePool
    {
        $client = new PredisClient(sprintf('tcp:/%s:%s', $config['server'], $config['port']));

        return new PredisCachePool($client);
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
        $adapter = $this->getContainer()->get($config['connection']);

        return new FilesystemCachePool(new Flysystem($adapter));
    }

    /**
     * Create an instance of the Memcached cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Memcached\MemcachedCachePool
     *
     * @codeCoverageIgnore
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
     * @return \Cache\Adapter\Memcache\MemcacheCachePool
     *
     * @codeCoverageIgnore
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
     * Create a prefixed cache pool with a namespace.
     *
     * @param \Cache\Hierarchy\HierarchicalPoolInterface $hierarchyPool
     * @param string                                     $namespace
     *
     * @return \Cache\Namespaced\NamespacedCachePool
     */
    protected function getNamespacedPool(HierarchicalPoolInterface $hierarchyPool, string $namespace): NamespacedCachePool
    {
        return new NamespacedCachePool($hierarchyPool, $namespace);
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
