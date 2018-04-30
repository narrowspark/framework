<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\HtmlResponse;

class HtmlResponseTest extends TestCase
{
    public function testConstructorAcceptsHtmlString(): void
    {
        $body     = '<html>Uh oh not found</html>';
        $response = new HtmlResponse($body);

        self::assertSame($body, (string) $response->getBody());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $body     = '<html>Uh oh not found</html>';
        $status   = 404;
        $response = new HtmlResponse($body, null, $status);

        self::assertEquals(404, $response->getStatusCode());
        self::assertSame($body, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $body    = '<html>Uh oh not found</html>';
        $status  = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new HtmlResponse($body, null, $status, $headers);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        self::assertEquals('text/html; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertEquals(404, $response->getStatusCode());
        self::assertSame($body, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $stream   = $this->getMockBuilder(StreamInterface::class)->getMock();
        $response = new HtmlResponse($stream);

        self::assertSame($stream, $response->getBody());
    }

    public function invalidHtmlContent()
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

    /**
     * @dataProvider invalidHtmlContent
     * @expectedException \InvalidArgumentException
     *
     * @param mixed $body
     */
    public function testRaisesExceptionforNonStringNonStreamBodyContent($body): void
    {
        new HtmlResponse($body);
    }

    public function testConstructorRewindsBodyStream(): void
    {
        $html     = '<p>test data</p>';
        $response = new HtmlResponse($html);

        $actual = $response->getBody()->getContents();
        self::assertEquals($html, $actual);
    }
}
