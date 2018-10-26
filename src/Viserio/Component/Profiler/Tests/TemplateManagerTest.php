<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests;

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

/**
 * @internal
 */
final class TemplateManagerTest extends MockeryTestCase
{
    public function testEscape(): void
    {
        $original = "This is a <a href=''>Foo</a> test string";

        $this->assertEquals(
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
        $this->assertLessThan(
            10,
            \abs(\mb_strlen($original) - \mb_strlen(TemplateManager::escape($original)))
        );
    }

    public function testRender(): void
    {
        $assets   = new AssetsRenderer();
        $template = new TemplateManager(
            [],
            \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resource' . \DIRECTORY_SEPARATOR . 'views' . \DIRECTORY_SEPARATOR . 'profiler.html.php',
            'fds4f6as',
            $assets->getIcons()
        );

        $this->assertSame(
            $this->removeId(\file_get_contents(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'View' . \DIRECTORY_SEPARATOR . 'profile.html')),
            $this->removeId($template->render())
        );
    }

    public function testRenderWithCollector(): void
    {
        $collector = new PhpInfoDataCollector();
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $assets   = new AssetsRenderer();
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

        require_once __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'View' . \DIRECTORY_SEPARATOR . 'profilewithcollector.html.php';

        $this->assertSame(
            $this->removeId($text),
            $this->removeId($template->render())
        );
    }

    public function testRenderWithAjaxRequestsDataCollector(): void
    {
        $collector = new AjaxRequestsDataCollector();
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $assets   = new AssetsRenderer();
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

        $this->assertSame(
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
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $assets   = new AssetsRenderer();
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

        $this->assertSame(
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
