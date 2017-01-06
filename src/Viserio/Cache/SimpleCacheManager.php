<?php
declare(strict_types=1);
namespace Viserio\Cache;

use Cache\Bridge\SimpleCache\SimpleCacheBridge;

class SimpleCacheManager extends CacheManager
{
    /**
     * {@inheritdoc}
     */
    public function createDriver(array $config)
    {
        $driver = parent::createDriver($config);

        return new SimpleCacheBridge($driver);
    }
}
