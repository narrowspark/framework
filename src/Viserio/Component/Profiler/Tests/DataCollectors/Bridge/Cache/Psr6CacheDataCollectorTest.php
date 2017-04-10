<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollectors\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\Psr6CacheDataCollector;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;

class Psr6CacheDataCollectorTest extends MockeryTestCase
{
    public function testGetMenu()
    {
        $collector = $this->getPsr6CacheDataCollector();

        static::assertSame(
            [
                'icon'  => 'ic_layers_white_24px.svg',
                'label' => '0 in',
                'value' => '0μs',
            ],
            $collector->getMenu()
        );
    }

    public function testGetTooltip()
    {
        $collector = $this->getPsr6CacheDataCollector();

        static::assertSame(
            '<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Cache calls</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Total time</b><span>0μs</span></div><div class="profiler-menu-tooltip-group-piece"><b>Cache hits</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Cache writes</b><span>0</span></div></div>',
            $collector->getTooltip()
        );
    }

    public function testGetPanel()
    {
        $collector = $this->getPsr6CacheDataCollector();

        static::assertSame(
            $this->removeTabId('<div class="profiler-tabs row"><div class="profiler-tabs-tab col span_12"><input type="radio" name="tabgroup" id="tab-0-58658cec676d8"><label for="tab-0-58658cec676d8">ArrayCachePool</label><div class="profiler-tabs-tab-content"><h3>Statistics</h3><ul class="metrics"><li class="metric"><span class="value">0</span><span class="label">calls</span></li><li class="metric"><span class="value">0μs</span><span class="label">time</span></li><li class="metric"><span class="value">0</span><span class="label">reads</span></li><li class="metric"><span class="value">0</span><span class="label">hits</span></li><li class="metric"><span class="value">0</span><span class="label">misses</span></li><li class="metric"><span class="value">0</span><span class="label">writes</span></li><li class="metric"><span class="value">0</span><span class="label">deletes</span></li><li class="metric"><span class="value">N/A</span><span class="label">hits/reads</span></li></ul><h3>Calls</h3><div class="empty">Empty</div></div></div></div>'),
            $this->removeSymfonyVarDumper($collector->getPanel())
        );
    }

    private function removeTabId(string $html): string
    {
        return trim(preg_replace('/="tab-0(.*?)"/', '', $html));
    }

    private function getPsr6CacheDataCollector()
    {
        $collector = new Psr6CacheDataCollector();
        $collector->addPool(new TraceableCacheItemDecorater(new ArrayCachePool()));
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        return $collector;
    }

    private function removeSymfonyVarDumper(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/', '', $html);

        return $this->removeTabId(preg_replace('/id=sf-dump-(?:\d+) /', '', $html));
    }
}
