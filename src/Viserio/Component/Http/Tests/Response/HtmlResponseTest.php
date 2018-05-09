<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\HtmlResponse;

class HtmlResponseTest extends TestCase
{
    /**
     * @var string
     */
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
        $status   = 404;
        $response = new HtmlResponse($this->htmlString, null, $status);

        self::assertEquals($status, $response->getStatusCode());
        self::assertSame($this->htmlString, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $status  = 404;
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
        $stream   = $this->getMockBuilder(StreamInterface::class)->getMock();
        $response = new HtmlResponse($stream);

        self::assertSame($stream, $response->getBody());
    }

    /**
     * @dataProvider invalidContentProvider
     * @expectedException \Viserio\Component\Contract\Http\Exception\InvalidArgumentException
     *
     * @param mixed $body
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent($body): void
    {
        new HtmlResponse($body);
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

    public function testConstructorRewindsBodyStream(): void
    {
        $response = new HtmlResponse($this->htmlString);

        $actual = $response->getBody()->getContents();

        self::assertEquals($this->htmlString, $actual);
    }
}
