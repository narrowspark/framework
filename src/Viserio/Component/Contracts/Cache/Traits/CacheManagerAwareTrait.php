<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Cache\Traits;

use RuntimeException;
use Viserio\Component\Contracts\Cache\Manager;

trait CacheManagerAwareTrait
{
    /**
     * Cache Manager instance.
     *
     * @var \Viserio\Component\Contracts\Cache\Manager|null
     */
    protected $cacheManager;

    /**
     * Set a Cache Manager.
     *
     * @param \Viserio\Component\Contracts\Cache\Manager $cacheManager
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
     * @return \Viserio\Component\Contracts\Cache\Manager
     */
    public function getCacheManager(): Manager
    {
        if (! $this->cacheManager) {
            throw new RuntimeException('Cache Manager is not set up.');
        }

        return $this->cacheManager;
    }
}
