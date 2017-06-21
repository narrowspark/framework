<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\SimpleTraceableCacheDecorator;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTestTrait;

class SimpleTraceableCacheDecoratorTest extends TestCase
{
    use SimpleTraceableCacheDecoratorTestTrait;

    protected function createSimpleCache()
    {
        return new SimpleTraceableCacheDecorator(new ArrayCachePool());
    }
}
