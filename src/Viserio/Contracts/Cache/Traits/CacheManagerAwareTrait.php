<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cache\Traits;

use RuntimeException;
use Viserio\Contracts\Cache\Manager;

trait CacheManagerAwareTrait
{
    /**
     * Cache Manager instance.
     *
     * @var \Viserio\Contracts\Cache\Manager|null
     */
    protected $cacheManager;

    /**
     * Set a Cache Manager.
     *
     * @param \Viserio\Contracts\Cache\Manager $cacheManager
     *
     * @return $this
     */
    public function setCacheManager(Manager $cacheManager)
    {
        $this->cacheManager = $cacheManager;

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
        if (! $this->cacheManager) {
            throw new RuntimeException('Cache Manager is not set up.');
        }

        return $this->cacheManager;
    }
}
