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
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\Psr6Psr16CacheDataCollector;
use Viserio\Component\Profiler\DataCollector\Bridge\Cache\TraceableCacheItemDecorator;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class Psr6Psr16CacheDataCollectorTest extends MockeryTestCase
{
    public function testGetMenu(): void
    {
        $collector = $this->getPsr6CacheDataCollector();

        self::assertSame(
            [
                'icon' => 'ic_layers_white_24px.svg',
                'label' => '0 in',
                'value' => '0μs',
            ],
            $collector->getMenu()
        );
    }

    public function testGetTooltip(): void
    {
        $collector = $this->getPsr6CacheDataCollector();

        self::assertSame(
            '<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Cache calls</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Total time</b><span>0μs</span></div><div class="profiler-menu-tooltip-group-piece"><b>Cache hits</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Cache writes</b><span>0</span></div></div>',
            $collector->getTooltip()
        );
    }

    private function removeTabId(string $html): string
    {
        return \trim(\preg_replace('/="tab-0(.*?)"/', '', $html));
    }

    private function getPsr6CacheDataCollector()
    {
        $collector = new Psr6Psr16CacheDataCollector();
        $collector->addPool(new TraceableCacheItemDecorator(new ArrayCachePool()));
        $collector->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        return $collector;
    }
}
