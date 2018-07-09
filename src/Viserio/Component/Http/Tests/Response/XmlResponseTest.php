<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\XmlResponse;

/**
 * @internal
 */
final class XmlResponseTest extends TestCase
{
    /**
     * @var string
     */
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

        static::assertSame($this->xmlString, (string) $response->getBody());
        static::assertEquals(200, $response->getStatusCode());
        static::assertEquals('text/xml; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $status   = 404;
        $response = new XmlResponse($this->xmlString, null, $status);

        static::assertEquals($status, $response->getStatusCode());
        static::assertSame($this->xmlString, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $status  = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new XmlResponse($this->xmlString, null, $status, $headers);

        static::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        static::assertEquals('text/xml; charset=utf-8', $response->getHeaderLine('content-type'));
        static::assertEquals($status, $response->getStatusCode());
        static::assertSame($this->xmlString, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $stream   = $this->getMockBuilder(StreamInterface::class)->getMock();
        $response = new XmlResponse($stream);

        static::assertSame($stream, $response->getBody());
    }

    /**
     * @dataProvider invalidContentProvider
     *
     * @param mixed $body
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent($body): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\InvalidArgumentException::class);

        new XmlResponse($body);
    }

    public function invalidContentProvider(): array
    {
        return [
            'null'       => [null],
            'true'       => [true],
            'false'      => [false],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['php://temp']],
            'object'     => [(object) ['php://temp']],
        ];
    }
}
