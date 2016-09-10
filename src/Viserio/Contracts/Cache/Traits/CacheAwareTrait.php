<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cache\Traits;

use RuntimeException;
use Viserio\Contracts\Cache\Manager;

trait CacheAwareTrait
{
    /**
     * Cache Manager instance.
     *
     * @var \Viserio\Contracts\Cache\Manager|null
     */
    protected $cache;

    /**
     * Set a Cache Manager.
     *
     * @param \Viserio\Contracts\Cache\Manager $cache
     *
     * @return $this
     */
    public function setCacheManager(Manager $cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * Get the Cache Manager.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Contracts\Cache\Manager
     */
    public function getCacheManager(): Manager
    {
        if (! $this->cache) {
            throw new RuntimeException('Cache Manager is not set up.');
        }

        return $this->cache;
    }
}
