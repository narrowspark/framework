<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response\TextResponse;

/**
 * @internal
 */
final class TextResponseTest extends TestCase
{
    /**
     * @var string
     */
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

        $this->assertSame($this->string, (string) $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/plain; charset=utf-8', $response->getHeaderLine('Content-Type'));
    }

    public function testConstructorAllowsPassingStatus(): void
    {
        $status   = 404;
        $response = new TextResponse($this->string, null, $status);

        $this->assertEquals($status, $response->getStatusCode());
        $this->assertSame($this->string, (string) $response->getBody());
    }

    public function testConstructorAllowsPassingHeaders(): void
    {
        $status  = 404;
        $headers = [
            'x-custom' => ['foo-bar'],
        ];
        $response = new TextResponse($this->string, null, $status, $headers);

        $this->assertEquals(['foo-bar'], $response->getHeader('x-custom'));
        $this->assertEquals('text/plain; charset=utf-8', $response->getHeaderLine('content-type'));
        $this->assertEquals($status, $response->getStatusCode());
        $this->assertSame($this->string, (string) $response->getBody());
    }

    public function testAllowsStreamsForResponseBody(): void
    {
        $stream   = $this->getMockBuilder(StreamInterface::class)->getMock();
        $response = new TextResponse($stream);

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

        new TextResponse($body);
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
