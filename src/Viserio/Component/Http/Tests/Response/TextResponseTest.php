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
use Viserio\Component\Http\Response\TextResponse;
use Viserio\Component\Http\Tests\Response\Traits\StreamBodyContentCasesTrait;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class TextResponseTest extends MockeryTestCase
{
    use StreamBodyContentCasesTrait;

    /** @var string */
    private $string;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->string = 'this is a text';
    }

    public function testConstructorAcceptsXmlString(): void
    {
        $response = new TextResponse($this->string);

        self::assertSame($this->string, (string) $response->getBody());
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $status = 404;
        $response = new TextResponse($this->string, null, $status);

        self::assertEquals($status, $response->getStatusCode());
        self::assertSame($this->string, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $status = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new TextResponse($this->string, null, $status, $headers);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        self::assertEquals('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertEquals($status, $response->getStatusCode());
        self::assertSame($this->string, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $stream = Mockery::mock(StreamInterface::class);
        $response = new TextResponse($stream);

        self::assertSame($stream, $response->getBody());
    }

    /**
     * @dataProvider provideRaisesExceptionForNonStringNonStreamBodyContentCases
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent($body): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TextResponse($body);
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public function provideRaisesExceptionForNonStringNonStreamBodyContentCases(): iterable
    {
        return $this->getNonStreamBodyContentCases();
    }
}
