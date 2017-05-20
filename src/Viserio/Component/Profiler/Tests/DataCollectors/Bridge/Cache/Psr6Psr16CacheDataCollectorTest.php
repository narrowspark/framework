<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests\DataCollectors\Bridge\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\Psr6Psr16CacheDataCollector;
use Viserio\Component\Profiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorator;

class Psr6Psr16CacheDataCollectorTest extends MockeryTestCase
{
    public function testGetMenu()
    {
        $collector = $this->getPsr6CacheDataCollector();

        self::assertSame(
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

        self::assertSame(
            '<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Cache calls</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Total time</b><span>0μs</span></div><div class="profiler-menu-tooltip-group-piece"><b>Cache hits</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Cache writes</b><span>0</span></div></div>',
            $collector->getTooltip()
        );
    }

    private function removeTabId(string $html): string
    {
        return trim(preg_replace('/="tab-0(.*?)"/', '', $html));
    }

    private function getPsr6CacheDataCollector()
    {
        $collector = new Psr6Psr16CacheDataCollector();
        $collector->addPool(new TraceableCacheItemDecorator(new ArrayCachePool()));
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
