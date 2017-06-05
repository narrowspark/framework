<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Twig\Environment;
use Twig\Extension\ProfilerExtension;
use Twig\Loader\ArrayLoader;
use Twig\Profiler\Profile;
use Viserio\Bridge\Twig\DataCollector\TwigDataCollector;

class TwigDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition()
    {
        $collect = $this->getTwigDataCollector();

        self::assertSame(
            [
                'icon'  => file_get_contents(__DIR__ . '/../../DataCollector/Resources/icons/ic_view_quilt_white_24px.svg'),
                'label' => 'Twig',
                'value' => '',
            ],
            $collect->getMenu()
        );
        self::assertSame('left', $collect->getMenuPosition());
    }

    public function testGetTooltip()
    {
        $collect = $this->getTwigDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        self::assertSame('<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Template calls</b><span>1</span></div><div class="profiler-menu-tooltip-group-piece"><b>Block calls</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Macro calls</b><span>0</span></div></div>', $collect->getTooltip());
    }

    public function testGetProfile()
    {
        $collect = $this->getTwigDataCollector();

        self::assertInstanceOf(Profile::class, $collect->getProfile());
    }

    private function removeTabId(string $html): string
    {
        return trim(preg_replace('/="tab-(.*?)"/', '', $html));
    }

    private function getTwigDataCollector()
    {
        $profile = new Profile();
        $env     = new Environment(
            new ArrayLoader(['test.twig' => 'test'])
        );
        $env->addExtension(new ProfilerExtension($profile));

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
