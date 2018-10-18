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

/**
 * @internal
 */
final class TwigDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition(): void
    {
        $collect = $this->getTwigDataCollector();

        static::assertSame(
            [
                'icon'  => \file_get_contents(\dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'icons' . \DIRECTORY_SEPARATOR . 'ic_view_quilt_white_24px.svg'),
                'label' => 'Twig',
                'value' => '',
            ],
            $collect->getMenu()
        );
        static::assertSame('left', $collect->getMenuPosition());
    }

    public function testGetTooltip(): void
    {
        $collect = $this->getTwigDataCollector();
        $collect->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertSame('<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Template calls</b><span>1</span></div><div class="profiler-menu-tooltip-group-piece"><b>Block calls</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Macro calls</b><span>0</span></div></div>', $collect->getTooltip());
    }

    public function testGetProfile(): void
    {
        $collect = $this->getTwigDataCollector();

        static::assertInstanceOf(Profile::class, $collect->getProfile());
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
}
