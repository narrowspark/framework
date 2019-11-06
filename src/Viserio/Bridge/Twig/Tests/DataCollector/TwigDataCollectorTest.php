<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Bridge\Twig\Tests\DataCollector;

use Mockery;
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
 *
 * @small
 */
final class TwigDataCollectorTest extends MockeryTestCase
{
    public function testGetMenuAndPosition(): void
    {
        $collect = $this->getTwigDataCollector();

        self::assertSame(
            [
                'icon' => \file_get_contents(\dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'icons' . \DIRECTORY_SEPARATOR . 'ic_view_quilt_white_24px.svg'),
                'label' => 'Twig',
                'value' => '',
            ],
            $collect->getMenu()
        );
        self::assertSame('left', $collect->getMenuPosition());
    }

    public function testGetTooltip(): void
    {
        $collect = $this->getTwigDataCollector();
        $collect->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        self::assertSame('<div class="profiler-menu-tooltip-group"><div class="profiler-menu-tooltip-group-piece"><b>Template calls</b><span>1</span></div><div class="profiler-menu-tooltip-group-piece"><b>Block calls</b><span>0</span></div><div class="profiler-menu-tooltip-group-piece"><b>Macro calls</b><span>0</span></div></div>', $collect->getTooltip());
    }

    public function testGetProfile(): void
    {
        $collect = $this->getTwigDataCollector();
        $profile = $collect->getProfile();

        self::assertSame('ROOT', $profile->getType());
    }

    private function getTwigDataCollector(): TwigDataCollector
    {
        $profile = new Profile();
        $env = new Environment(
            new ArrayLoader(['test.twig' => 'test'])
        );
        $env->addExtension(new ProfilerExtension($profile));

        $template = $env->load('test.twig');
        $template->render([]);

        return new TwigDataCollector($profile, $env);
    }
}
