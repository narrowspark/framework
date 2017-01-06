<?php
declare(strict_types=1);
namespace Viserio\Contracts\Cache;

use Cache\Adapter\Chain\CachePoolChain;
use Psr\SimpleCache\CacheInterface;

interface Manager
{
    /**
     *  Chain multiple PSR-6 Cache pools together for performance.
     *
     * @param array      $pools
     * @param array|null $options
     *
     * @return \Cache\Adapter\Chain\CachePoolChain
     */
    public function chain(array $pools, ?array $options = null): CachePoolChain;
}
