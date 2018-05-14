<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem\Cache;

use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Cached\Storage\Adapter;
use League\Flysystem\Cached\Storage\Psr6Cache;
use Viserio\Component\Filesystem\FilesystemManager;
use Viserio\Contract\Cache\Manager as CacheManagerContract;
use Viserio\Contract\Filesystem\Exception\InvalidArgumentException;

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
     * @var null|\Viserio\Contract\Cache\Manager
     */
    protected $cacheManager;

    /**
     * Create a new cached factory instance.
     *
     * @param \Viserio\Component\Filesystem\FilesystemManager $manager
     * @param null|\Viserio\Contract\Cache\Manager            $cacheManager
     */
    public function __construct(FilesystemManager $manager, CacheManagerContract $cacheManager = null)
    {
        $this->manager = $manager;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Establish a cache connection.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \League\Flysystem\Cached\CacheInterface
     */
    public function getConnection(array $config): CacheInterface
    {
        if (! isset($config['cache']['driver'])) {
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
