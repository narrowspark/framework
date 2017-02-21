<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\WebProfiler\AssetsRenderer;
use Viserio\Component\WebProfiler\DataCollectors\PhpInfoDataCollector;
use Viserio\Component\WebProfiler\TemplateManager;
use Viserio\Component\WebProfiler\WebProfiler;

class WebProfilerTest extends MockeryTestCase
{
    public function testSetAndGetUrlGenerator()
    {
        $profiler = $this->getWebProfiler();

        $profiler->setUrlGenerator($this->mock(UrlGeneratorContract::class));

        static::assertInstanceof(UrlGeneratorContract::class, $profiler->getUrlGenerator());
    }

    public function testSetAndGetTemplate()
    {
        $profiler = $this->getWebProfiler();

        $profiler->setTemplate(__DIR__);

        static::assertSame(__DIR__, $profiler->getTemplate());
    }

    public function testAddHasAndGetCollectors()
    {
        $profiler  = $this->getWebProfiler();
        $collector = new PhpInfoDataCollector();

        $profiler->addCollector($collector);

        static::assertTrue($profiler->hasCollector('php-info-data-collector'));

        static::assertSame(
            [
                'php-info-data-collector' => [
                    'collector' => $collector,
                    'priority'  => 100,
                ],
            ],
            $profiler->getCollectors()
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage [php-info-data-collector] is already a registered collector.
     */
    public function testAddCollectorThrowsException()
    {
        $profiler  = $this->getWebProfiler();
        $collector = new PhpInfoDataCollector();

        $profiler->addCollector($collector);
        $profiler->addCollector($collector);
    }

    public function testModifyResponse()
    {
        $assets   = new AssetsRenderer();
        $profiler = new WebProfiler($assets);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $profiler->enable();
        $response = (new ResponseFactory())->createResponse(200);

        $response = $profiler->modifyResponse(
            (new ServerRequestFactory())->createServerRequest($server),
            $response
        );

        $template   = new TemplateManager(
            [],
            $profiler->getTemplate(),
            '12213435415',
            $assets->getIcons()
        );

        $renderedContent = $assets->render() . $template->render();

        static::assertEquals(
            $this->removeId($renderedContent),
            $this->removeId((string) $response->getBody())
        );
    }

    public function testModifyResponseWithOldContent()
    {
        $assets   = new AssetsRenderer();
        $profiler = new WebProfiler($assets);

        $profiler->enable();
        $stream = (new StreamFactory())->createStream(
            '<!DOCTYPE html><html><head><title></title></head><body></body></html>'
        );
        $response = (new ResponseFactory())->createResponse(200);
        $response = $response->withBody($stream);
        $profiler->setStreamFactory(new StreamFactory());

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $response = $profiler->modifyResponse(
            (new ServerRequestFactory())->createServerRequest($server),
            $response
        );

        $template   = new TemplateManager(
            [],
            $profiler->getTemplate(),
            '12213435415',
            $assets->getIcons()
        );

        $renderedContent = $assets->render() . $template->render();

        static::assertEquals(
            $this->removeId('<!DOCTYPE html><html><head><title></title></head><body>' . $renderedContent . '</body></html>'),
            $this->removeId((string) $response->getBody())
        );
    }

    public function testDontModifyResponse()
    {
        $assets   = new AssetsRenderer();
        $profiler = new WebProfiler($assets);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $profiler->disable();
        $orginalResponse = (new ResponseFactory())->createResponse(200);

        $response = $profiler->modifyResponse(
            (new ServerRequestFactory())->createServerRequest($server),
            $orginalResponse
        );

        static::assertEquals($response, $orginalResponse);
    }

    private function removeId(string $html): string
    {
        return trim(preg_replace('/="webprofiler-(.*?)"/', '', $html));
    }

    private function getWebProfiler()
    {
        return new WebProfiler(new AssetsRenderer());
    }
}
