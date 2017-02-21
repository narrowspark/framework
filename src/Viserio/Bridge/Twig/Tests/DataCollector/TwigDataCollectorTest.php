<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\DataCollector;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig_Environment;
use Twig_Extension_Profiler;
use Twig_Loader_Array;
use Twig_Profiler_Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;

class TwigDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition()
    {
        $collect = $this->getTwigDataCollector();

        static::assertSame(
            [
                'icon'  => file_get_contents(__DIR__ . '/../../DataCollector/Resources/icons/ic_view_quilt_white_24px.svg'),
                'label' => 'Twig',
                'value' => '',
            ],
            $collect->getMenu()
        );
        static::assertSame('left', $collect->getMenuPosition());
    }

    public function testGetTooltip()
    {
        $collect = $this->getTwigDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame('<div class="webprofiler-menu-tooltip-group"><div class="webprofiler-menu-tooltip-group-piece"><b>Template calls</b><span>1</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Block calls</b><span>0</span></div><div class="webprofiler-menu-tooltip-group-piece"><b>Macro calls</b><span>0</span></div></div>', $collect->getTooltip());
    }

    public function testGetPanel()
    {
        $profile = new Twig_Profiler_Profile();
        $env     = new Twig_Environment(
            new Twig_Loader_Array(['test.twig' => 'test'])
        );
        $env->addExtension(new Twig_Extension_Profiler($profile));

        $template = $env->load('test.twig');
        $template->render([]);

        $collect = new TwigDataCollector($profile, $env);
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $expect = preg_replace('/(\r\n|\n\r|\r)/', "\n", '<div class="webprofiler-tabs row"><div class="webprofiler-tabs-tab col span_6"><input type="radio" name="tabgroup" id="tab-0-58a60028922b9"><label for="tab-0-58a60028922b9">Twig <span class="counter">1</span></label><div class="webprofiler-tabs-tab-content"><h3>Twig Metrics</h3><ul class="metrics"><li class="metric"><span class="value">' . $this->formatDuration($collect->getTime()) . '</span><span class="label">Render time</span></li><li class="metric"><span class="value">1</span><span class="label">Template calls</span></li><li class="metric"><span class="value">0</span><span class="label">Block calls</span></li><li class="metric"><span class="value">0</span><span class="label">Macro calls</span></li></ul><h3>Rendered Templates</h3><table class="row"><thead><tr><th scope="col" class="Template Name">Template Name</th><th scope="col" class="Render Count">Render Count</th></tr></thead><tbody><tr><th>test.twig</th><td>1</td></tr></tbody></table><div class="twig-graph"><h3>Rendering Call Graph</h3>' . $collect->getHtmlCallGraph() . '</div></div></div><div class="webprofiler-tabs-tab col span_6"><input type="radio" name="tabgroup" id="tab-1-58a60028922c0"><label for="tab-1-58a60028922c0">Twig Extensions <span class="counter">4</span></label><div class="webprofiler-tabs-tab-content"><table class="row"><thead><tr><th scope="col" class="Extension">Extension</th></tr></thead><tbody><tr><td>Twig_Extension_Core</td></tr><tr><td>Twig_Extension_Escaper</td></tr><tr><td>Twig_Extension_Optimizer</td></tr><tr><td>Twig_Extension_Profiler</td></tr></tbody></table></div></div></div>');

        static::assertSame($this->removeTabId($expect), $this->removeTabId(preg_replace('/(\r\n|\n\r|\r)/', "\n", $collect->getPanel())));
    }

    public function testGetProfile()
    {
        $collect = $this->getTwigDataCollector();

        static::assertInstanceOf(Twig_Profiler_Profile::class, $collect->getProfile());
    }

    private function removeTabId(string $html): string
    {
        return trim(preg_replace('/="tab-(.*?)"/', '', $html));
    }

    private function getTwigDataCollector()
    {
        $profile = new Twig_Profiler_Profile();
        $env     = new Twig_Environment(
            new Twig_Loader_Array(['test.twig' => 'test'])
        );
        $env->addExtension(new Twig_Extension_Profiler($profile));

        $template = $env->load('test.twig');
        $template->render([]);

        return new TwigDataCollector($profile, $env);
    }

    /**
     * Add measurement to float time.
     *
     * @param float $seconds
     *
     * @return string
     *
     * @codeCoverageIgnore
     */
    private function formatDuration(float $seconds): string
    {
        if ($seconds < 0.001) {
            return round($seconds * 1000000) . 'μs';
        } elseif ($seconds < 1) {
            return round($seconds * 1000, 2) . 'ms';
        }

        return round($seconds, 2) . 's';
    }
}
