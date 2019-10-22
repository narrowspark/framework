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

namespace Viserio\Component\Exception\Tests\Filter;

use Exception;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Filter\CanDisplayFilter;

/**
 * @internal
 *
 * @small
 */
final class CanDisplayFilterTest extends MockeryTestCase
{
    /** @var \Psr\Http\Message\ServerRequestInterface */
    private $serverRequest;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serverRequest = \Mockery::mock(ServerRequestInterface::class);
    }

    public function testFirstIsRemoved(): void
    {
        $exception = new Exception();

        $html = $this->arrangeHtmlDisplayer($exception, false);
        $json = $this->arrangeJsonDisplayer($exception);

        $displayers = $this->arrangeDisplayerFilter($html, $json, $exception);

        self::assertSame([$json], $displayers);
    }

    public function testNoChange(): void
    {
        $exception = new Exception();

        $html = $this->arrangeHtmlDisplayer($exception, true);
        $json = $this->arrangeJsonDisplayer($exception);

        $displayers = $this->arrangeDisplayerFilter($html, $json, $exception);

        self::assertSame([$html, $json], $displayers);
    }

    /**
     * @param \Throwable $exception
     *
     * @return \Mockery\MockInterface
     */
    private function arrangeJsonDisplayer(Throwable $exception): MockInterface
    {
        $json = \Mockery::mock(JsonDisplayer::class);
        $json->shouldReceive('canDisplay')
            ->once()
            ->with($exception, $exception, 500)
            ->andReturn(true);

        return $json;
    }

    /**
     * @param \Throwable $exception
     * @param bool       $return
     *
     * @return \Mockery\MockInterface
     */
    private function arrangeHtmlDisplayer(Throwable $exception, bool $return): MockInterface
    {
        $html = \Mockery::mock(HtmlDisplayer::class);
        $html->shouldReceive('canDisplay')
            ->once()
            ->with($exception, $exception, 500)
            ->andReturn($return);

        return $html;
    }

    /**
     * @param \Viserio\Component\Exception\Displayer\HtmlDisplayer $html
     * @param \Viserio\Component\Exception\Displayer\JsonDisplayer $json
     * @param \Throwable                                           $exception
     *
     * @return array
     */
    private function arrangeDisplayerFilter(HtmlDisplayer $html, JsonDisplayer $json, Throwable $exception): array
    {
        return (new CanDisplayFilter())->filter([$html, $json], $this->serverRequest, $exception, $exception, 500);
    }
}
