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

namespace Viserio\Component\Profiler\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\DataCollector\AjaxRequestsDataCollector;
use Viserio\Component\Profiler\DataCollector\Bridge\SwiftMailDataCollector;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;
use Viserio\Component\Profiler\TemplateManager;
use const DIRECTORY_SEPARATOR;

/**
 * @internal
 *
 * @small
 */
final class TemplateManagerTest extends MockeryTestCase
{
    public function testEscape(): void
    {
        $original = "This is a <a href=''>Foo</a> test string";

        self::assertEquals(
            TemplateManager::escape($original),
            'This is a &lt;a href=&#039;&#039;&gt;Foo&lt;/a&gt; test string'
        );
    }

    public function testEscapeBrokenUtf8(): void
    {
        // The following includes an illegal utf-8 sequence to test.
        // Encoded in base64 to survive possible encoding changes of this file.
        $original = \base64_decode('VGhpcyBpcyBhbiBpbGxlZ2FsIHV0Zi04IHNlcXVlbmNlOiDD', true);

        // Test that the escaped string is kinda similar in length, not empty
        self::assertLessThan(
            10,
            \abs(\strlen($original) - \strlen(TemplateManager::escape($original)))
        );
    }

    public function testRender(): void
    {
        $assets = new AssetsRenderer();
        $template = new TemplateManager(
            [],
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . 'profiler.html.php',
            'fds4f6as',
            $assets->getIcons()
        );

        self::assertSame(
            $this->removeId(\file_get_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'View' . \DIRECTORY_SEPARATOR . 'profile.html')),
            $this->removeId($template->render())
        );
    }

//    public function testRenderWithCollector(): void
//    {
//        $collector = new PhpInfoDataCollector();
//        $collector->collect(
//            Mockery::mock(ServerRequestInterface::class),
//            Mockery::mock(ResponseInterface::class)
//        );
//
//        $assets = new AssetsRenderer();
//        $template = new TemplateManager(
//            [
//                'php-info-data-collector' => [
//                    'collector' => $collector,
//                ],
//            ],
//            \dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Resource' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'profiler.html.php',
//            'fds4f6as',
//            $assets->getIcons()
//        );
//
//        require_once __DIR__ . DIRECTORY_SEPARATOR . 'Fixture' . DIRECTORY_SEPARATOR . 'View' . DIRECTORY_SEPARATOR . 'profilewithcollector.html.php';
//
//        self::assertSame(
//            $this->removeId($text),
//            $this->removeId($template->render())
//        );
//    }

    public function testRenderWithAjaxRequestsDataCollector(): void
    {
        $collector = new AjaxRequestsDataCollector();
        $collector->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        $assets = new AssetsRenderer();
        $template = new TemplateManager(
            [
                'php-info-data-collector' => [
                    'collector' => $collector,
                ],
            ],
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . 'profiler.html.php',
            'fds4f6as',
            $assets->getIcons()
        );

        self::assertSame(
            $this->removeId(\file_get_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'View' . \DIRECTORY_SEPARATOR . 'profilewithajaxcollector.html.php')),
            $this->removeId($template->render())
        );
    }

    public function testRenderWithAPanelCollector(): void
    {
        $collector = new SwiftMailDataCollector(
            new Swift_Mailer(new Swift_SmtpTransport('smtp.example.org', 25))
        );
        $collector->collect(
            Mockery::mock(ServerRequestInterface::class),
            Mockery::mock(ResponseInterface::class)
        );

        $assets = new AssetsRenderer();
        $template = new TemplateManager(
            [
                'php-info-data-collector' => [
                    'collector' => $collector,
                ],
            ],
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . 'profiler.html.php',
            'fds4f6as',
            $assets->getIcons()
        );

        self::assertSame(
            $this->removeId(\file_get_contents(__DIR__ . '' . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'View' . \DIRECTORY_SEPARATOR . 'profilewithpanelcollector.html.php')),
            $this->removeId($template->render())
        );
    }

    private function removeId(string $html): string
    {
        $html = \preg_replace('/[ \t]+/', ' ', \preg_replace('/[\r\n]+/', "\n", $html));

        return \trim(\preg_replace('/="profiler-(.*?)"/', '', $html));
    }
}
