<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cache\Traits;

use Psr\Cache\CacheItemPoolInterface;

trait CachePoolAwareTrait
{
    /**
     * A cache pool instance.
     *
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    protected $cachePool;
}
