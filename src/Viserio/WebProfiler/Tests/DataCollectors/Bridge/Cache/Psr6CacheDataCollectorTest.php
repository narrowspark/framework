<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors\Bridge\Recording;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\Psr6CacheDataCollector;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\TraceableCacheItemDecorater;

class Psr6CacheDataCollectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        // Verify Mockery expectations.
        Mock::close();
    }

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
            '<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>Cache calls</b><span>0</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Total time</b><span>0μs</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Cache hits</b><span>0</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Cache writes</b><span>0</span></div></div>',
            $collector->getTooltip()
        );
    }

    public function testGetPanel()
    {
        $collector = $this->getPsr6CacheDataCollector();

        static::assertSame(
            '<h3>Statistics for "Cache\Adapter\PHPArray\ArrayCachePool"</h3><ul class="metrics"><li class="metric"><span class="value">0</span><span class="label">calls</span></li><li class="metric"><span class="value">0μs</span><span class="label">time</span></li><li class="metric"><span class="value">0</span><span class="label">reads</span></li><li class="metric"><span class="value">0</span><span class="label">hits</span></li><li class="metric"><span class="value">0</span><span class="label">misses</span></li><li class="metric"><span class="value">0</span><span class="label">writes</span></li><li class="metric"><span class="value">0</span><span class="label">deletes</span></li><li class="metric"><span class="value">N/A</span><span class="label">hits/reads</span></li></ul><h3>Calls for "Cache\Adapter\PHPArray\ArrayCachePool"</h3><div class="empty">Empty</div>',
            $this->removeSymfonyVarDumper($collector->getPanel())
        );
    }

    private function getPsr6CacheDataCollector()
    {
        $collector = new Psr6CacheDataCollector(new Stopwatch());
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

        return trim(preg_replace('/id=sf-dump-(?:\d+) /', '', $html));
    }
}
