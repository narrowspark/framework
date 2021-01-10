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
     * @return static
     */
    public function setCacheItemPool(CacheItemPoolInterface $cachePool): self
    {
        $this->cacheItemPool = $cachePool;

        return $this;
    }
}
