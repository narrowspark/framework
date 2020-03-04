<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Cache\Traits;

use Viserio\Contract\Cache\Manager;

trait CacheManagerAwareTrait
{
    /**
     * Cache Manager instance.
     *
     * @var null|\Viserio\Contract\Cache\Manager
     */
    protected $cacheManager;

    /**
     * Set a Cache Manager.
     *
     * @return static
     */
    public function setCacheManager(Manager $cacheManager): self
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }
}
