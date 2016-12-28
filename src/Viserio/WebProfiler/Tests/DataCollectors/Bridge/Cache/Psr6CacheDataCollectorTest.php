<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests\DataCollectors\Bridge\Recording;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Cache\Adapter\PHPArray\ArrayCachePool;
use Viserio\WebProfiler\DataCollectors\Bridge\Cache\Psr6CacheDataCollector;
use Symfony\Component\Stopwatch\Stopwatch;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
                'icon' => '',
                'label' => '0 in',
                'value' => '0μs'
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
            '<h3>Cache\Adapter\PHPArray\ArrayCachePool</h3><table class="row"><thead><tr><th scope="col" class="Calls">Calls</th><th scope="col" class="Time">Time</th><th scope="col" class="Reads">Reads</th><th scope="col" class="Hits">Hits</th><th scope="col" class="Misses">Misses</th><th scope="col" class="Writes">Writes</th><th scope="col" class="Deletes">Deletes</th><th scope="col" class="Ratio">Ratio</th></tr></thead><tbody><tr><td><pre class=sf-dump data-indent-pad="  "><span class=sf-dump-num>0</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad="  "><span class=sf-dump-num>0</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad="  "><span class=sf-dump-num>0</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad="  "><span class=sf-dump-num>0</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad="  "><span class=sf-dump-num>0</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad="  "><span class=sf-dump-num>0</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad="  "><span class=sf-dump-num>0</span>
</pre>
</td><td><pre class=sf-dump data-indent-pad="  ">"<span class=sf-dump-str title="3 characters">N/A</span>"
</pre>
</td></tr></tbody></table>',
            $this->removeSymfonyVarDumper($collector->getPanel())
        );
    }

    private function getPsr6CacheDataCollector()
    {
        $collector = new Psr6CacheDataCollector(new Stopwatch());
        $collector->addPool(new ArrayCachePool());
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
