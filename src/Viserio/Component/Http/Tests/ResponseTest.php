<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;

/**
 * @internal
 */
final class ResponseTest extends AbstractMessageTest
{
    protected function setUP(): void
    {
        parent::setUp();

        $this->classToTest = new Response();
    }

    public function testResponseImplementsInterface(): void
    {
        $this->assertInstanceOf(ResponseInterface::class, $this->classToTest);
    }

    public function testValidDefaultStatusCode(): void
    {
        $message    = $this->classToTest;
        $statusCode = $message->getStatusCode();

        $this->assertInternalType('integer', $statusCode, 'getStatusCode must return an integer');
    }

    public function testValidDefaultReasonPhrase(): void
    {
        $message      = $this->classToTest;
        $reasonPhrase = $message->getReasonPhrase();

        $this->assertInternalType('string', $reasonPhrase, 'getReasonPhrase must return a string');
    }

    // Test methods for change instances status
    public function testValidWithStatusDefaultReasonPhrase(): void
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $statusCode   = 100;
        $newMessage   = $message->withStatus($statusCode);

        $this->assertImmutable($messageClone, $message, $newMessage);
        $this->assertEquals(
            $statusCode,
            $newMessage->getStatusCode(),
            'getStatusCode does not match code set in withStatus'
        );
    }

    public function testValidWithStatusCustomReasonPhrase(): void
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $statusCode   = 100;
        $reasonPhrase = 'example';
        $newMessage   = $message->withStatus($statusCode, $reasonPhrase);

        $this->assertImmutable($messageClone, $message, $newMessage);
        $this->assertEquals(
            $statusCode,
            $newMessage->getStatusCode(),
            'getStatusCode does not match code set in withStatus'
        );
        $this->assertEquals(
            $reasonPhrase,
            $newMessage->getReasonPhrase(),
            'getReasonPhrase does not match code set in withStatus'
        );
    }

    public function testDefaultConstructor(): void
    {
        $response = $this->classToTest;

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('1.1', $response->getProtocolVersion());
        $this->assertSame('OK', $response->getReasonPhrase());
        $this->assertSame([], $response->getHeaders());
        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('', (string) $response->getBody());
    }

    public function testCanConstructWithStatusCode(): void
    {
        $response = new Response(404);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Not Found', $response->getReasonPhrase());
    }

    public function testConstructorDoesNotReadStreamBody(): void
    {
        $streamIsRead = false;
        $body         = FnStream::decorate(new Stream(\fopen('php://temp', 'rb+')), [
            '__toString' => function () use (&$streamIsRead) {
                $streamIsRead = true;

                return '';
            },
        ]);

        $response = new Response(200, [], $body);

        $this->assertFalse($streamIsRead);
        $this->assertSame($body, $response->getBody());
    }

    public function testCanConstructWithHeaders(): void
    {
        $response = new Response(200, ['Foo' => 'Bar']);

        $this->assertSame(['Foo' => ['Bar']], $response->getHeaders());
        $this->assertSame('Bar', $response->getHeaderLine('Foo'));
        $this->assertSame(['Bar'], $response->getHeader('Foo'));
    }

    public function testCanConstructWithHeadersAsArray(): void
    {
        $response = new Response(200, [
            'Foo' => ['baz', 'bar'],
        ]);

        $this->assertSame(['Foo' => ['baz', 'bar']], $response->getHeaders());
        $this->assertSame('baz,bar', $response->getHeaderLine('Foo'));
        $this->assertSame(['baz', 'bar'], $response->getHeader('Foo'));
    }

    public function testCanConstructWithBody(): void
    {
        $response = new Response(200, [], 'baz');

        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('baz', (string) $response->getBody());
    }

    public function testNullBody(): void
    {
        $response = new Response(200, [], null);

        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('', (string) $response->getBody());
    }

    public function testFalseyBody(): void
    {
        $response = new Response(200, [], '0');

        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('0', (string) $response->getBody());
    }

    public function testWithStatusCodeAndNoReason(): void
    {
        $response = (new Response())->withStatus(201);

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Created', $response->getReasonPhrase());
    }

    public function testWithStatusCodeAndReason(): void
    {
        $response = (new Response())->withStatus(201, 'Foo');

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('Foo', $response->getReasonPhrase());

        $response = (new Response())->withStatus(201, '0');

        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame('0', $response->getReasonPhrase(), 'Falsey reason works');
    }

    public function testWithProtocolVersion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid HTTP version. Must be one of: 1.0, 1.1, 2.0');

        (new Response())->withProtocolVersion('1000');
    }

    public function testSameInstanceWhenSameProtocol(): void
    {
        $response = new Response();

        $this->assertSame($response, $response->withProtocolVersion('1.1'));
    }

    public function testWithBody(): void
    {
        $body   = '0';
        $stream = \fopen('php://temp', 'rb+');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $response = (new Response())->withBody(new Stream($stream));

        $this->assertInstanceOf(StreamInterface::class, $response->getBody());
        $this->assertSame('0', (string) $response->getBody());
    }

    public function testSameInstanceWhenSameBody(): void
    {
        $response = new Response();
        $body     = $response->getBody();

        $this->assertSame($response, $response->withBody($body));
    }

    public function testWithHeader(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('baZ', 'Bam');

        $this->assertSame(['Foo' => ['Bar']], $response->getHeaders());
        $this->assertSame(['Foo' => ['Bar'], 'baZ' => ['Bam']], $response2->getHeaders());
        $this->assertSame('Bam', $response2->getHeaderLine('baz'));
        $this->assertSame(['Bam'], $response2->getHeader('baz'));
    }

    public function testWithHeaderAsArray(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('baZ', ['Bam', 'Bar']);

        $this->assertSame(['Foo' => ['Bar']], $response->getHeaders());
        $this->assertSame(['Foo' => ['Bar'], 'baZ' => ['Bam', 'Bar']], $response2->getHeaders());
        $this->assertSame('Bam,Bar', $response2->getHeaderLine('baz'));
        $this->assertSame(['Bam', 'Bar'], $response2->getHeader('baz'));
    }

    public function testWithHeaderReplacesDifferentCase(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('foO', 'Bam');

        $this->assertSame(['Foo' => ['Bar']], $response->getHeaders());
        $this->assertSame(['foO' => ['Bam']], $response2->getHeaders());
        $this->assertSame('Bam', $response2->getHeaderLine('foo'));
        $this->assertSame(['Bam'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeader(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('foO', 'Baz');

        $this->assertSame(['Foo' => ['Bar']], $response->getHeaders());
        $this->assertSame(['Foo' => ['Bar', 'Baz']], $response2->getHeaders());
        $this->assertSame('Bar,Baz', $response2->getHeaderLine('foo'));
        $this->assertSame(['Bar', 'Baz'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeaderAsArray(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('foO', ['Baz', 'Bam']);

        $this->assertSame(['Foo' => ['Bar']], $response->getHeaders());
        $this->assertSame(['Foo' => ['Bar', 'Baz', 'Bam']], $response2->getHeaders());
        $this->assertSame('Bar,Baz,Bam', $response2->getHeaderLine('foo'));
        $this->assertSame(['Bar', 'Baz', 'Bam'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeaderThatDoesNotExist(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('nEw', 'Baz');

        $this->assertSame(['Foo' => ['Bar']], $response->getHeaders());
        $this->assertSame(['Foo' => ['Bar'], 'nEw' => ['Baz']], $response2->getHeaders());
        $this->assertSame('Baz', $response2->getHeaderLine('new'));
        $this->assertSame(['Baz'], $response2->getHeader('new'));
    }

    public function testWithoutHeaderThatExists(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar', 'Baz' => 'Bam']);
        $response2 = $response->withoutHeader('foO');

        $this->assertTrue($response->hasHeader('foo'));
        $this->assertSame(['Foo' => ['Bar'], 'Baz' => ['Bam']], $response->getHeaders());
        $this->assertFalse($response2->hasHeader('foo'));
        $this->assertSame(['Baz' => ['Bam']], $response2->getHeaders());
    }

    public function testWithoutHeaderThatDoesNotExist(): void
    {
        $response  = new Response(200, ['Baz' => 'Bam']);
        $response2 = $response->withoutHeader('foO');

        $this->assertSame($response, $response2);
        $this->assertFalse($response2->hasHeader('foo'));
        $this->assertSame(['Baz' => ['Bam']], $response2->getHeaders());
    }

    public function testSameInstanceWhenRemovingMissingHeader(): void
    {
        $response = new Response();

        $this->assertSame($response, $response->withoutHeader('foo'));
    }

    public function testHeaderValuesAreTrimmed(): void
    {
        $response1 = new Response(200, ['OWS' => " \t \tFoo\t \t "]);
        $response2 = (new Response())->withHeader('OWS', " \t \tFoo\t \t ");
        $response3 = (new Response())->withAddedHeader('OWS', " \t \tFoo\t \t ");

        foreach ([$response1, $response2, $response3] as $response) {
            $this->assertSame(['OWS' => ['Foo']], $response->getHeaders());
            $this->assertSame('Foo', $response->getHeaderLine('OWS'));
            $this->assertSame(['Foo'], $response->getHeader('OWS'));
        }
    }
}
