<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache;

use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTestTrait;
use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\SimpleTraceableCacheDecorator;
use Psr\SimpleCache\CacheInterface;

class SimpleTraceableCacheDecoratorTest extends TestCase
{
    use SimpleTraceableCacheDecoratorTestTrait;

    protected function createSimpleCache(): CacheInterface
    {
        return new SimpleTraceableCacheDecorator(new ArrayCachePool());
    }
}
