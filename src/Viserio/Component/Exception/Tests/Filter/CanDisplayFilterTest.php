<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Exception\Tests\Filter;

use Exception;
use Mockery;
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
 * @coversNothing
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

        $this->serverRequest = Mockery::mock(ServerRequestInterface::class);
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

    private function arrangeJsonDisplayer(Throwable $exception): MockInterface
    {
        $json = Mockery::mock(JsonDisplayer::class);
        $json->shouldReceive('canDisplay')
            ->once()
            ->with($exception, $exception, 500)
            ->andReturn(true);

        return $json;
    }

    private function arrangeHtmlDisplayer(Throwable $exception, bool $return): MockInterface
    {
        $html = Mockery::mock(HtmlDisplayer::class);
        $html->shouldReceive('canDisplay')
            ->once()
            ->with($exception, $exception, 500)
            ->andReturn($return);

        return $html;
    }

    private function arrangeDisplayerFilter(HtmlDisplayer $html, JsonDisplayer $json, Throwable $exception): array
    {
        return (new CanDisplayFilter())->filter([$html, $json], $this->serverRequest, $exception, $exception, 500);
    }
}
