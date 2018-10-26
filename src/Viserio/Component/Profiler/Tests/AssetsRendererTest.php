<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\DataCollector\AjaxRequestsDataCollector;
use Viserio\Component\Routing\Generator\UrlGenerator;

/**
 * @internal
 */
final class AssetsRendererTest extends MockeryTestCase
{
    public function testSetndGetIcon(): void
    {
        $assets = new AssetsRenderer();

        $assets->setIcon('ic_clear_white_24px.svg', __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Icons' . \DIRECTORY_SEPARATOR);

        $this->assertSame(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'Icons' . \DIRECTORY_SEPARATOR . 'ic_clear_white_24px.svg', $assets->getIcons()['ic_clear_white_24px.svg']);
    }

    public function testSetAndGetIgnoredCollectors(): void
    {
        $assets = new AssetsRenderer();

        $assets->setIgnoredCollector('test');

        $this->assertSame('test', $assets->getIgnoredCollectors()[0]);
    }

    public function testGetAssets(): void
    {
        $profiler = $this->mock(ProfilerContract::class);
        $profiler->shouldReceive('getCollectors')
            ->twice()
            ->andReturn([]);
        $assets = new AssetsRenderer(false, __DIR__);
        $assets->setProfiler($profiler);

        $cssAssets = [
            __DIR__ . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'profiler.css',
            __DIR__ . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'profiler-grid.css',
        ];
        $jsAssets = [
            __DIR__ . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . 'zepto.min.js',
            __DIR__ . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . 'profiler.js',
        ];

        $this->assertSame($cssAssets, $assets->getAssets('css'));
        $this->assertSame($jsAssets, $assets->getAssets('js'));
    }

    public function testGetAssetsFromCollectors(): void
    {
        $profiler = $this->mock(ProfilerContract::class);
        $profiler->shouldReceive('getCollectors')
            ->once()
            ->andReturn([
                'ajax' => [
                    'collector' => new AjaxRequestsDataCollector(),
                    'priority'  => 100,
                ],
            ]);
        $assets = new AssetsRenderer(true, __DIR__);
        $assets->setProfiler($profiler);

        $cssAssets = [
            __DIR__ . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'profiler.css',
            __DIR__ . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'profiler-grid.css',
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'css' . \DIRECTORY_SEPARATOR . 'ajax-requests.css',
        ];
        $jsAssets = [
            __DIR__ . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . 'profiler.js',
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'js' . \DIRECTORY_SEPARATOR . 'ajaxHandler.js',
        ];

        $this->assertSame([$cssAssets, $jsAssets], $assets->getAssets());
    }

    public function testRenderWithUrlGenerator(): void
    {
        $generator = $this->mock(UrlGenerator::class);
        $generator->shouldReceive('generate')
            ->once()
            ->andReturn('path_css');
        $generator->shouldReceive('generate')
            ->once()
            ->andReturn('path_js');
        $profiler = $this->mock(ProfilerContract::class);
        $profiler->shouldReceive('getUrlGenerator')
            ->once()
            ->andReturn($generator);
        $profiler->shouldReceive('getCollectors')
            ->twice();
        $assets = new AssetsRenderer();
        $assets->setProfiler($profiler);

        $this->assertSame('<link rel="stylesheet" type="text/css" property="stylesheet" href="path_css"><script type="text/javascript" src="path_js"></script>', $assets->render());
    }
}
