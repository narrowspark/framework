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

namespace Viserio\Component\Http\Tests\Response;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\XmlResponse;
use Viserio\Component\Http\Tests\Response\Traits\StreamBodyContentCasesTrait;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class XmlResponseTest extends MockeryTestCase
{
    use StreamBodyContentCasesTrait;

    /** @var string */
    private $xmlString;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->xmlString = '<?xml version="1.0"?>
<data>
  <to>Tove</to>
  <from>Jani</from>
  <heading>Reminder</heading>
</data>
            ';
    }

    public function testConstructorAcceptsXmlString(): void
    {
        $response = new XmlResponse($this->xmlString);

        self::assertSame($this->xmlString, (string) $response->getBody());
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('text/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $status = 404;
        $response = new XmlResponse($this->xmlString, null, $status);

        self::assertEquals($status, $response->getStatusCode());
        self::assertSame($this->xmlString, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $status = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new XmlResponse($this->xmlString, null, $status, $headers);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        self::assertEquals('text/xml; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertEquals($status, $response->getStatusCode());
        self::assertSame($this->xmlString, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\StreamInterface $streamMock */
        $streamMock = $this->mock(StreamInterface::class);

        $response = new XmlResponse($streamMock);

        self::assertSame($streamMock, $response->getBody());
    }

    /**
     * @dataProvider provideRaisesExceptionForNonStringNonStreamBodyContentCases
     *
     * @param mixed $body
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent($body): void
    {
        $this->expectException(InvalidArgumentException::class);

        new XmlResponse($body);
    }

    /**
     * @return iterable<array<string, mixed>>
     */
    public function provideRaisesExceptionForNonStringNonStreamBodyContentCases(): iterable
    {
        return $this->getNonStreamBodyContentCases();
    }
}
