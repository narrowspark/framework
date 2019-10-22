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

namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\PhpCacheTraceableCacheDecorator;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\SimpleTraceableCacheDecoratorTestTrait;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\TraceableCacheItemDecoratorTestTrait;

/**
 * @internal
 *
 * @small
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

        self::assertCount(1, $calls);

        $call = $calls[0];

        self::assertSame('invalidateTags', $call->name);
        self::assertTrue($call->result);
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
        self::assertTrue($call->result);
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
