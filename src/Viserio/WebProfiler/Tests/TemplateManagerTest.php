<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests;

use Viserio\WebProfiler\AssetsRenderer;
use Viserio\WebProfiler\TemplateManager;
use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\WebProfiler\DataCollectors\PhpInfoDataCollector;
use Viserio\WebProfiler\DataCollectors\AjaxRequestsDataCollector;

class TemplateManagerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
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

        static::assertSame(
            $this->removeId(file_get_contents(__DIR__ . '/Fixture/View/profilewithcollector.html')),
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

    private function removeId(string $html): string
    {
        $html = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $html));

        return trim(preg_replace('/="webprofiler-(.*?)"/', '', $html));
    }
}
