<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Cache\Traits;

use RuntimeException;
use Viserio\Component\Contract\Cache\Manager;

trait CacheManagerAwareTrait
{
    /**
     * Cache Manager instance.
     *
     * @var null|\Viserio\Component\Contract\Cache\Manager
     */
    protected $cacheManager;

    /**
     * Get the Cache Manager.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contract\Cache\Manager
     */
    public function getCacheManager(): Manager
    {
        if ($this->cacheManager === null) {
            throw new RuntimeException('Cache Manager is not set up.');
        }

        return $this->cacheManager;
    }

    /**
     * Set a Cache Manager.
     *
     * @param \Viserio\Component\Contract\Cache\Manager $cacheManager
     *
     * @return $this
     */
    public function setCacheManager(Manager $cacheManager)
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }
}
