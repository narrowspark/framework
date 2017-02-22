<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;
use Viserio\Component\Routing\UrlGenerator;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\WebProfiler\AssetsRenderer;
use Viserio\Component\WebProfiler\DataCollectors\AjaxRequestsDataCollector;

class AssetsRendererTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    public function testSetndGetIcon()
    {
        $assets = new AssetsRenderer();

        $assets->setIcon('ic_clear_white_24px.svg', __DIR__ . 'Fixture/Icons/');

        static::assertSame(self::normalizePath(__DIR__ . 'Fixture/Icons/ic_clear_white_24px.svg'), $assets->getIcons()['ic_clear_white_24px.svg']);
    }

    public function testSetAndGetIgnoredCollectors()
    {
        $assets = new AssetsRenderer();

        $assets->setIgnoredCollector('test');

        static::assertSame('test', $assets->getIgnoredCollectors()[0]);
    }

    public function testGetAssets()
    {
        $profiler = $this->mock(WebProfilerContract::class);
        $profiler->shouldReceive('getCollectors')
            ->twice()
            ->andReturn([]);
        $assets = new AssetsRenderer(false, __DIR__);
        $assets->setWebProfiler($profiler);

        $cssAssets = [
            __DIR__ . '/css/webprofiler.css',
            __DIR__ . '/css/webprofiler-grid.css',
        ];
        $jsAssets = [
            __DIR__ . '/js/zepto.min.js',
            __DIR__ . '/js/webprofiler.js',
        ];

        static::assertSame($cssAssets, $assets->getAssets('css'));
        static::assertSame($jsAssets, $assets->getAssets('js'));
    }

    public function testGetAssetsFromCollectors()
    {
        $profiler = $this->mock(WebProfilerContract::class);
        $profiler->shouldReceive('getCollectors')
            ->once()
            ->andReturn([new AjaxRequestsDataCollector()]);
        $assets = new AssetsRenderer(true, __DIR__);
        $assets->setWebProfiler($profiler);

        $cssAssets = [
            __DIR__ . '/css/webprofiler.css',
            __DIR__ . '/css/webprofiler-grid.css',
            str_replace('Tests', 'DataCollectors', __DIR__) . '/../Resources/css/ajax-requests.css',
        ];
        $jsAssets = [
            __DIR__ . '/js/webprofiler.js',
            str_replace('Tests', 'DataCollectors', __DIR__) . '/../Resources/js/ajaxHandler.js',
        ];

        static::assertSame([$cssAssets, $jsAssets], $assets->getAssets());
    }

    public function testRenderWithUrlGenerator()
    {
        $generator = $this->mock(UrlGenerator::class);
        $generator->shouldReceive('route')
            ->once()
            ->andReturn('path_css');
        $generator->shouldReceive('route')
            ->once()
            ->andReturn('path_js');
        $profiler = $this->mock(WebProfilerContract::class);
        $profiler->shouldReceive('getUrlGenerator')
            ->once()
            ->andReturn($generator);
        $profiler->shouldReceive('getCollectors')
            ->twice();
        $assets = new AssetsRenderer();
        $assets->setWebProfiler($profiler);

        static::assertSame('<link rel="stylesheet" type="text/css" property="stylesheet" href="path_css"><script type="text/javascript" src="path_js"></script>', $assets->render());
    }
}
