<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCacheItemDecorator;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\TraceableCacheItemDecoratorTestTrait;

class TraceableCacheItemDecoratorTest extends TestCase
{
    use TraceableCacheItemDecoratorTestTrait;

    protected function createCachePool()
    {
        return new TraceableCacheItemDecorator(new ArrayCachePool());
    }
}
