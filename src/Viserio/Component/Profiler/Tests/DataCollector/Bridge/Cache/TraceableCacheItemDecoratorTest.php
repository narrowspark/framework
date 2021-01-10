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

namespace Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCacheItemDecorator;
use Viserio\Component\Profiler\Tests\DataCollector\Bridge\Cache\Traits\TraceableCacheItemDecoratorTestTrait;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class TraceableCacheItemDecoratorTest extends TestCase
{
    use TraceableCacheItemDecoratorTestTrait;

    protected function createCachePool()
    {
        return new TraceableCacheItemDecorator(new ArrayCachePool());
    }
}
