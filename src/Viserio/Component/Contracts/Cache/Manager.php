<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Cache;

use Cache\Adapter\Chain\CachePoolChain;
use Viserio\Component\Contracts\Support\Manager as BaseManager;

interface Manager extends BaseManager
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
