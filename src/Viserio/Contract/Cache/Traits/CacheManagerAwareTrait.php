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
     * @param \Viserio\Contract\Cache\Manager $cacheManager
     *
     * @return static
     */
    public function setCacheManager(Manager $cacheManager): self
    {
        $this->cacheManager = $cacheManager;

        return $this;
    }
}
