<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Profiler\DataCollector;
use Viserio\Component\Contract\Routing\UrlGenerator as UrlGeneratorContract;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\ServerRequestFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;
use Viserio\Component\Profiler\TemplateManager;
use Viserio\Component\Profiler\Tests\Fixture\ProfilerTester as Profiler;

class ProfilerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Profiler\Tests\Fixture\ProfilerTester
     */
    private $profiler;

    public function setUp(): void
    {
        parent::setUp();

        $this->profiler = new Profiler(new AssetsRenderer());
    }

    public function testSetAndGetUrlGenerator(): void
    {
        $this->profiler->setUrlGenerator($this->mock(UrlGeneratorContract::class));

        self::assertInstanceOf(UrlGeneratorContract::class, $this->profiler->getUrlGenerator());
    }

    public function testSetAndGetTemplate(): void
    {
        $this->profiler->setTemplate(__DIR__);

        self::assertSame(__DIR__, $this->profiler->getTemplate());
    }

    public function testAddHasAndGetCollectors(): void
    {
        $collector = new PhpInfoDataCollector();

        $this->profiler->addCollector($collector);

        self::assertTrue($this->profiler->hasCollector('php-info-data-collector'));

        self::assertSame(
            [
                'php-info-data-collector' => [
                    'collector' => $collector,
                    'priority'  => 100,
                ],
            ],
            $this->profiler->getCollectors()
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage [php-info-data-collector] is already a registered collector.
     */
    public function testAddCollectorThrowsException(): void
    {
        $collector = new PhpInfoDataCollector();

        $this->profiler->addCollector($collector);
        $this->profiler->addCollector($collector);
    }

    public function testModifyResponse(): void
    {
        $assets   = new AssetsRenderer();
        $profiler = new Profiler($assets);

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $profiler->enable();
        $response = (new ResponseFactory())->createResponse();

        $response = $profiler->modifyResponse(
            (new ServerRequestFactory())->createServerRequestFromArray($server),
            $response
        );

        $template   = new TemplateManager(
            [],
            $profiler->getTemplate(),
            '12213435415',
            $assets->getIcons()
        );

        $renderedContent = $assets->render() . $template->render();

        self::assertEquals(
            $this->removeId($renderedContent),
            $this->removeId((string) $response->getBody())
        );
    }

    public function testModifyResponseWithOldContent(): void
    {
        $assets   = new AssetsRenderer();
        $profiler = new Profiler($assets);

        $profiler->enable();
        $stream = (new StreamFactory())->createStream(
            '<!DOCTYPE html><html><head><title></title></head><body></body></html>'
        );
        $response = (new ResponseFactory())->createResponse();
        $response = $response->withBody($stream);
        $profiler->setStreamFactory(new StreamFactory());

        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $response = $profiler->modifyResponse(
            (new ServerRequestFactory())->createServerRequestFromArray($server),
            $response
        );

        $template   = new TemplateManager(
            [],
            $profiler->getTemplate(),
            '12213435415',
            $assets->getIcons()
        );

        $renderedContent = $assets->render() . $template->render();

        self::assertEquals(
            $this->removeId('<!DOCTYPE html><html><head><title></title></head><body>' . $renderedContent . '</body></html>'),
            $this->removeId((string) $response->getBody())
        );
    }

    public function testDontModifyResponse(): void
    {
        $server                = $_SERVER;
        $server['SERVER_ADDR'] = '127.0.0.1';
        unset($server['PHP_SELF']);

        $this->profiler->disable();
        $orginalResponse = (new ResponseFactory())->createResponse();

        $response = $this->profiler->modifyResponse(
            (new ServerRequestFactory())->createServerRequestFromArray($server),
            $orginalResponse
        );

        self::assertEquals($response, $orginalResponse);
    }

    public function testFlush(): void
    {
        $collector = $this->mock(DataCollector::class);
        $collector->shouldReceive('getName')
            ->twice()
            ->andReturn('mock');
        $collector->shouldReceive('flush')
            ->once();

        $this->profiler->addCollector($collector);
        $this->profiler->flush();
    }

    private function removeId(string $html): string
    {
        return \trim(\preg_replace('/="profiler-(.*?)"/', '', $html));
    }
}
