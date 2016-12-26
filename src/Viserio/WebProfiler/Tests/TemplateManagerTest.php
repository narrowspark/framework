<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Tests;

use Viserio\WebProfiler\AssetsRenderer;
use Viserio\WebProfiler\TemplateManager;

class TemplateManagerTest extends \PHPUnit_Framework_TestCase
{
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

    private function removeId(string $html): string
    {
        $html = preg_replace('/[ \t]+/', ' ', preg_replace('/[\r\n]+/', "\n", $html));

        return trim(preg_replace('/="webprofiler-(.*?)"/', '', $html));
    }
}
