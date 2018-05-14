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

use Psr\Cache\CacheItemPoolInterface;

trait CacheItemPoolAwareTrait
{
    /**
     * A CacheItemPool instance.
     *
     * @var null|\Psr\Cache\CacheItemPoolInterface
     */
    protected $cacheItemPool;

    /**
     * Set a CacheItemPool.
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cachePool
     *
     * @return static
     */
    public function setCacheItemPool(CacheItemPoolInterface $cachePool): self
    {
        $this->cacheItemPool = $cachePool;

        return $this;
    }
}
