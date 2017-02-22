<?php
declare(strict_types=1);
namespace Viserio\Component\Cache;

use Cache\Adapter\Apc\ApcCachePool;
use Cache\Adapter\Apcu\ApcuCachePool;
use Cache\Adapter\Chain\CachePoolChain;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use Cache\Adapter\Memcache\MemcacheCachePool;
use Cache\Adapter\Memcached\MemcachedCachePool;
use Cache\Adapter\MongoDB\MongoDBCachePool;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Predis\PredisCachePool;
use Cache\Adapter\Redis\RedisCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\Encryption\EncryptedCachePool;
use Cache\Hierarchy\HierarchicalPoolInterface;
use Cache\Namespaced\NamespacedCachePool;
use Defuse\Crypto\Key;
use Interop\Container\ContainerInterface;
use League\Flysystem\Filesystem as Flysystem;
use Memcache;
use Memcached;
use MongoDB\Driver\Manager as MongoDBManager;
use Predis\Client as PredisClient;
use Psr\Log\LoggerAwareInterface;
use Redis;
use RuntimeException;
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
     * @param \Interop\Container\ContainerInterface $container
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
     * Get a encrypted driver instance.
     *
     * @param string|null $driver
     *
     * @return \Cache\Encryption\EncryptedCachePool
     */
    public function getEncryptedDriver(string $driver = null): EncryptedCachePool
    {
        if (class_exists(EncryptedCachePool::class)) {
            if ($this->options['key'] === false) {
                throw new RuntimeException('No encryption key found.');
            }

            return new EncryptedCachePool($this->getDriver($driver), Key::loadFromAsciiSafeString($this->options['key']));
        }

        throw new RuntimeException('"Cache\Encryption\EncryptedCachePool" class not found.');
    }

    /**
     * {@inheritdoc}
     */
    public function chain(array $pools, ?array $options = null): CachePoolChain
    {
        $resolvedPools = [];

        foreach ($pools as $pool) {
            if (is_string($pool)) {
                $resolvedPools[] = $this->getDriver($pool);
            } else {
                $resolvedPools[] = $pool;
            }
        }

        return new CachePoolChain(
            $resolvedPools,
            $options ?? (array) $this->options['chain_options'] ?? []
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createDriver(array $config)
    {
        $driver    = parent::createDriver($config);
        $namespace = $this->options['namespace'];

        if (class_exists(NamespacedCachePool::class) && $namespace && $driver instanceof HierarchicalPoolInterface) {
            $driver = $this->namespacedPool($driver, $namespace);
        }

        if ($this->logger !== null) {
            $driver->setLogger($this->getLogger());
        }

        return $driver;
    }

    /**
     * Create an instance of the Apc cache driver.
     *
     * @param array $config
     *
     * @return \Cache\Adapter\Apc\ApcCachePool
     *
     * @codeCoverageIgnore
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
     *
     * @codeCoverageIgnore
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
    protected function namespacedPool(HierarchicalPoolInterface $hierarchyPool, $namespace): NamespacedCachePool
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
