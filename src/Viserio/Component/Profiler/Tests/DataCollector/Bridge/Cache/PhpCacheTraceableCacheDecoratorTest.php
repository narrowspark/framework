<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\PhpCacheTraceableCacheDecorator;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTestTrait;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\TraceableCacheItemDecoratorTestTrait;

class PhpCacheTraceableCacheDecoratorTest extends TestCase
{
    use TraceableCacheItemDecoratorTestTrait;
    use SimpleTraceableCacheDecoratorTestTrait;

    public function testInvalidateTags(): void
    {
        $pool = $this->createCachePool();
        $pool->invalidateTags(['k']);
        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('invalidateTags', $call->name);
        self::assertSame(true, $call->result);
        self::assertSame(0, $call->hits);
        self::assertSame(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    public function testInvalidateTag(): void
    {
        $pool = $this->createCachePool();
        $pool->invalidateTag('k');
        $calls = $pool->getCalls();

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('invalidateTag', $call->name);
        self::assertSame(true, $call->result);
        self::assertSame(0, $call->hits);
        self::assertSame(0, $call->misses);
        self::assertNotEmpty($call->start);
        self::assertNotEmpty($call->end);
    }

    protected function createCachePool()
    {
        return new PhpCacheTraceableCacheDecorator(new ArrayCachePool());
    }

    protected function createSimpleCache()
    {
        return new PhpCacheTraceableCacheDecorator(new ArrayCachePool());
    }
}
