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

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;
use Viserio\Component\Profiler\AssetsRenderer;
use Viserio\Component\Profiler\DataCollector\PhpInfoDataCollector;
use Viserio\Component\Profiler\TemplateManager;
use Viserio\Component\Profiler\Tests\Fixture\ProfilerTester as Profiler;
use Viserio\Contract\Profiler\DataCollector;
use Viserio\Contract\Routing\UrlGenerator as UrlGeneratorContract;

/**
 * @internal
 *
 * @small
 */
final class ProfilerTest extends MockeryTestCase
{
    /** @var \Viserio\Component\Profiler\Tests\Fixture\ProfilerTester */
    private $profiler;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->profiler = new Profiler(new AssetsRenderer());
    }

    public function testSetAndGetUrlGenerator(): void
    {
        $this->profiler->setUrlGenerator(\Mockery::mock(UrlGeneratorContract::class));

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
                    'priority' => 100,
                ],
            ],
            $this->profiler->getCollectors()
        );
    }

    public function testAddCollectorThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('[php-info-data-collector] is already a registered collector.');

        $collector = new PhpInfoDataCollector();

        $this->profiler->addCollector($collector);
        $this->profiler->addCollector($collector);
    }

    public function testModifyResponse(): void
    {
        $assets = new AssetsRenderer();
        $profiler = new Profiler($assets);

        $profiler->enable();

        $response = $profiler->modifyResponse(
            new ServerRequest('/'),
            $this->getHtmlResponse()
        );

        $template = new TemplateManager(
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
        $assets = new AssetsRenderer();
        $profiler = new Profiler($assets);

        $profiler->enable();
        $stream = (new StreamFactory())->createStream(
            '<!DOCTYPE html><html><head><title></title></head><body></body></html>'
        );
        $response = $this->getHtmlResponse();
        $response = $response->withBody($stream);
        $profiler->setStreamFactory(new StreamFactory());

        $response = $profiler->modifyResponse(
            new ServerRequest('/'),
            $response
        );

        $template = new TemplateManager(
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
        $this->profiler->disable();
        $orginalResponse = $this->getHtmlResponse();

        $response = $this->profiler->modifyResponse(
            new ServerRequest('/'),
            $orginalResponse
        );

        self::assertEquals($response, $orginalResponse);
    }

    public function testFlush(): void
    {
        $collector = \Mockery::mock(DataCollector::class);
        $collector->shouldReceive('getName')
            ->twice()
            ->andReturn('mock');
        $collector->shouldReceive('reset')
            ->once();

        $this->profiler->addCollector($collector);
        $this->profiler->reset();
    }

    private function removeId(string $html): string
    {
        return \trim(\preg_replace('/="profiler-(.*?)"/', '', $html));
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function getHtmlResponse(): ResponseInterface
    {
        $response = (new ResponseFactory())->createResponse();

        return $response->withHeader('Content-Type', 'text/html;charset=UTF-8');
    }
}
