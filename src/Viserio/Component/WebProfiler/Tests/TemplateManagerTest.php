<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_SmtpTransport;
use Viserio\Component\WebProfiler\AssetsRenderer;
use Viserio\Component\WebProfiler\DataCollectors\AjaxRequestsDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\Bridge\SwiftMailDataCollector;
use Viserio\Component\WebProfiler\DataCollectors\PhpInfoDataCollector;
use Viserio\Component\WebProfiler\TemplateManager;

class TemplateManagerTest extends MockeryTestCase
{
    public function testEscape()
    {
        $original = "This is a <a href=''>Foo</a> test string";

        self::assertEquals(
            TemplateManager::escape($original),
            'This is a &lt;a href=&#039;&#039;&gt;Foo&lt;/a&gt; test string'
        );
    }

    public function testEscapeBrokenUtf8()
    {
        // The following includes an illegal utf-8 sequence to test.
        // Encoded in base64 to survive possible encoding changes of this file.
        $original = base64_decode('VGhpcyBpcyBhbiBpbGxlZ2FsIHV0Zi04IHNlcXVlbmNlOiDD');

        // Test that the escaped string is kinda similar in length, not empty
        self::assertLessThan(
            10,
            abs(mb_strlen($original) - mb_strlen(TemplateManager::escape($original)))
        );
    }

    public function testRender()
    {
        $assets   = new AssetsRenderer();
        $template = new TemplateManager(
            [],
            __DIR__ . '/../Resources/views/webprofiler.html.php',
            'fds4f6as',
            $assets->getIcons()
        );

        static::assertSame(
            $this->removeId(file_get_contents(__DIR__ . '/Fixture/View/profile.html')),
            $this->removeId($template->render())
        );
    }

    public function testRenderWithCollector()
    {
        $collector = new PhpInfoDataCollector();
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $assets   = new AssetsRenderer();
        $template = new TemplateManager(
            ['php-info-data-collector' => $collector],
            __DIR__ . '/../Resources/views/webprofiler.html.php',
            'fds4f6as',
            $assets->getIcons()
        );

        require_once __DIR__ . '/Fixture/View/profilewithcollector.html.php';

        static::assertSame(
            $this->removeId($text),
            $this->removeId($template->render())
        );
    }

    public function testRenderWithAjaxRequestsDataCollector()
    {
        $collector = new AjaxRequestsDataCollector();
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $assets   = new AssetsRenderer();
        $template = new TemplateManager(
            ['php-info-data-collector' => $collector],
            __DIR__ . '/../Resources/views/webprofiler.html.php',
            'fds4f6as',
            $assets->getIcons()
        );

        static::assertSame(
            $this->removeId(file_get_contents(__DIR__ . '/Fixture/View/profilewithajaxcollector.html')),
            $this->removeId($template->render())
        );
    }

    public function testRenderWithAPanelCollector()
    {
        $collector = new SwiftMailDataCollector(
            Swift_Mailer::newInstance(Swift_SmtpTransport::newInstance('smtp.example.org', 25))
        );
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        $assets   = new AssetsRenderer();
        $template = new TemplateManager(
            ['php-info-data-collector' => $collector],
            __DIR__ . '/../Resources/views/webprofiler.html.php',
            'fds4f6as',
            $assets->getIcons()
        );

        static::assertSame(
            $this->removeId(file_get_contents(__DIR__ . '/Fixture/View/profilewithpanelcollector.html')),
            $this->removeId($template->render())
        );
    }

    private function removeId(string $html): string
    {
        $html = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $html));

        return trim(preg_replace('/="webprofiler-(.*?)"/', '', $html));
    }
}
