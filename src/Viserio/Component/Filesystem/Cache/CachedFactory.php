<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Cache;

use InvalidArgumentException;
use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Cached\Storage\Adapter;
use League\Flysystem\Cached\Storage\Psr6Cache;
use Viserio\Component\Contracts\Cache\Manager as CacheManagerContract;
use Viserio\Component\Filesystem\FilesystemManager;

class CachedFactory
{
    /**
     * Instance of FilesystemManager.
     *
     * @var \Viserio\Component\Filesystem\FilesystemManager
     */
    protected $manager;

    /**
     * Instance of CacheManager.
     *
     * @var null|\Viserio\Component\Contracts\Cache\Manager
     */
    protected $cacheManager;

    /**
     * Create a new cached factory instance.
     *
     * @param \Viserio\Component\Filesystem\FilesystemManager $manager
     * @param null|\Viserio\Component\Contracts\Cache\Manager $cacheManager
     */
    public function __construct(FilesystemManager $manager, CacheManagerContract $cacheManager = null)
    {
        $this->manager      = $manager;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Establish a cache connection.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \League\Flysystem\Cached\CacheInterface
     */
    public function getConnection(array $config): CacheInterface
    {
        if (! isset($config['cache'], $config['cache']['driver'])) {
            throw new InvalidArgumentException('A driver must be specified.');
        }

        return $this->createConnector($config);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \League\Flysystem\Cached\CacheInterface
     */
    protected function createConnector(array $config): CacheInterface
    {
        $cacheConfig = $config['cache'];

        if (($cache = $this->cacheManager) !== null) {
            if ($cache->hasDriver($cacheConfig['driver'])) {
                return new Psr6Cache(
                    $cache->getDriver($cacheConfig['driver']),
                    $cacheConfig['key'],
                    $cacheConfig['expire']
                );
            }
        }

        if ($this->manager->hasConnection($cacheConfig['driver'])) {
            return new Adapter(
                $this->manager->createConnection($config),
                $cacheConfig['key'],
                $cacheConfig['expire']
            );
        }

        throw new InvalidArgumentException(\sprintf('Unsupported driver [%s].', $cacheConfig['driver']));
    }
}
