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

namespace Viserio\Component\Http\Tests\Response;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\HtmlResponse;
use Viserio\Component\Http\Tests\Response\Traits\StreamBodyContentCasesTrait;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class HtmlResponseTest extends MockeryTestCase
{
    use StreamBodyContentCasesTrait;

    /** @var string */
    private $htmlString;

    protected function setUp(): void
    {
        parent::setUp();

        $this->htmlString = '<html>Uh oh not found</html>';
    }

    public function testConstructorAcceptsHtmlString(): void
    {
        $response = new HtmlResponse($this->htmlString);

        self::assertSame($this->htmlString, (string) $response->getBody());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $status = 404;
        $response = new HtmlResponse($this->htmlString, null, $status);

        self::assertEquals($status, $response->getStatusCode());
        self::assertSame($this->htmlString, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $status = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new HtmlResponse($this->htmlString, null, $status, $headers);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        self::assertEquals('text/html; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertEquals($status, $response->getStatusCode());
        self::assertSame($this->htmlString, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $stream = Mockery::mock(StreamInterface::class);
        $response = new HtmlResponse($stream);

        self::assertSame($stream, $response->getBody());
    }

    /**
     * @dataProvider provideRaisesExceptionForNonStringNonStreamBodyContentCases
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent($body): void
    {
        $this->expectException(InvalidArgumentException::class);

        new HtmlResponse($body);
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public function provideRaisesExceptionForNonStringNonStreamBodyContentCases(): iterable
    {
        return $this->getNonStreamBodyContentCases();
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $response = new HtmlResponse($this->htmlString);

        $actual = $response->getBody()->getContents();

        self::assertEquals($this->htmlString, $actual);
    }
}
