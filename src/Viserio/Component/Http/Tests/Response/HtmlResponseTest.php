<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\HtmlResponse;

/**
 * @internal
 */
final class HtmlResponseTest extends TestCase
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

        $this->assertSame($this->htmlString, (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $status   = 404;
        $response = new HtmlResponse($this->htmlString, null, $status);

        $this->assertEquals($status, $response->getStatusCode());
        $this->assertSame($this->htmlString, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $status  = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new HtmlResponse($this->htmlString, null, $status, $headers);

        $this->assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertEquals('text/html; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertSame($this->htmlString, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $stream   = $this->getMockBuilder(StreamInterface::class)->getMock();
        $response = new HtmlResponse($stream);

        $this->assertSame($stream, $response->getBody());
    }

    /**
     * @dataProvider invalidContentProvider
     *
     * @param mixed $body
     */
    public function testRaisesExceptionForNonStringNonStreamBodyContent($body): void
    {
        $this->expectException(\Viserio\Component\Contract\Http\Exception\InvalidArgumentException::class);

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

        $this->assertEquals($this->htmlString, $actual);
    }
}
