<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Response;

use Viserio\Http\Response\HtmlResponse;

class HtmlResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorAcceptsHtmlString()
    {
        $body     = '<html>Uh oh not found</html>';
        $response = new HtmlResponse($body);

        self::assertSame($body, (string) $response->getBody());
        self::assertEquals(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus()
    {
        $body     = '<html>Uh oh not found</html>';
        $status   = 404;
        $response = new HtmlResponse($body, $status);

        self::assertEquals(404, $response->getStatusCode());
        self::assertSame($body, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders()
    {
        $body    = '<html>Uh oh not found</html>';
        $status  = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new HtmlResponse($body, $status, $headers);

        self::assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        self::assertEquals('text/html; charset=utf-8', $response->getHeaderLine('content-type'));
        self::assertEquals(404, $response->getStatusCode());
        self::assertSame($body, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody()
    {
        $stream   = $this->getMockBuilder('Psr\Http\Message\StreamInterface')->getMock();
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
     * @param mixed $body
     */
    public function testRaisesExceptionforNonStringNonStreamBodyContent($body)
    {
        $response = new HtmlResponse($body);
    }

    public function testConstructorRewindsBodyStream()
    {
        $html     = '<p>test data</p>';
        $response = new HtmlResponse($html);

        $actual = $response->getBody()->getContents();
        self::assertEquals($html, $actual);
    }
}
