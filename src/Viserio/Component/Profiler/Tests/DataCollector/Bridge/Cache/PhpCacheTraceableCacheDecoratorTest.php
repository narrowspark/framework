<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\PhpCacheTraceableCacheDecorator;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTestTrait;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\TraceableCacheItemDecoratorTestTrait;

/**
 * @internal
 */
final class PhpCacheTraceableCacheDecoratorTest extends TestCase
{
    use TraceableCacheItemDecoratorTestTrait;
    use SimpleTraceableCacheDecoratorTestTrait;

    public function testInvalidateTags(): void
    {
        $pool = $this->createCachePool();
        $pool->invalidateTags(['k']);
        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertSame('invalidateTags', $call->name);
        static::assertTrue($call->result);
        static::assertSame(0, $call->hits);
        static::assertSame(0, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
    }

    public function testInvalidateTag(): void
    {
        $pool = $this->createCachePool();
        $pool->invalidateTag('k');
        $calls = $pool->getCalls();

        static::assertCount(1, $calls);

        $call = $calls[0];

        static::assertSame('invalidateTag', $call->name);
        static::assertTrue($call->result);
        static::assertSame(0, $call->hits);
        static::assertSame(0, $call->misses);
        static::assertNotEmpty($call->start);
        static::assertNotEmpty($call->end);
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
