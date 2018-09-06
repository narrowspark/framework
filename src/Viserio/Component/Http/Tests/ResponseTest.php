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
        static::assertInstanceOf(ResponseInterface::class, $this->classToTest);
    }

    public function testValidDefaultStatusCode(): void
    {
        $message    = $this->classToTest;
        $statusCode = $message->getStatusCode();

        static::assertInternalType('integer', $statusCode, 'getStatusCode must return an integer');
    }

    public function testValidDefaultReasonPhrase(): void
    {
        $message      = $this->classToTest;
        $reasonPhrase = $message->getReasonPhrase();

        static::assertInternalType('string', $reasonPhrase, 'getReasonPhrase must return a string');
    }

    // Test methods for change instances status
    public function testValidWithStatusDefaultReasonPhrase(): void
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $statusCode   = 100;
        $newMessage   = $message->withStatus($statusCode);

        $this->assertImmutable($messageClone, $message, $newMessage);
        static::assertEquals(
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
        static::assertEquals(
            $statusCode,
            $newMessage->getStatusCode(),
            'getStatusCode does not match code set in withStatus'
        );
        static::assertEquals(
            $reasonPhrase,
            $newMessage->getReasonPhrase(),
            'getReasonPhrase does not match code set in withStatus'
        );
    }

    public function testDefaultConstructor(): void
    {
        $response = $this->classToTest;

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('1.1', $response->getProtocolVersion());
        static::assertSame('OK', $response->getReasonPhrase());
        static::assertSame([], $response->getHeaders());
        static::assertInstanceOf(StreamInterface::class, $response->getBody());
        static::assertSame('', (string) $response->getBody());
    }

    public function testCanConstructWithStatusCode(): void
    {
        $response = new Response(404);

        static::assertSame(404, $response->getStatusCode());
        static::assertSame('Not Found', $response->getReasonPhrase());
    }

    public function testConstructorDoesNotReadStreamBody(): void
    {
        $streamIsRead = false;
        $body         = FnStream::decorate(new Stream(\fopen('php://temp', 'r+b')), [
            '__toString' => function () use (&$streamIsRead) {
                $streamIsRead = true;

                return '';
            },
        ]);

        $response = new Response(200, [], $body);

        static::assertFalse($streamIsRead);
        static::assertSame($body, $response->getBody());
    }

    public function testCanConstructWithHeaders(): void
    {
        $response = new Response(200, ['Foo' => 'Bar']);

        static::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        static::assertSame('Bar', $response->getHeaderLine('Foo'));
        static::assertSame(['Bar'], $response->getHeader('Foo'));
    }

    public function testCanConstructWithHeadersAsArray(): void
    {
        $response = new Response(200, [
            'Foo' => ['baz', 'bar'],
        ]);

        static::assertSame(['Foo' => ['baz', 'bar']], $response->getHeaders());
        static::assertSame('baz,bar', $response->getHeaderLine('Foo'));
        static::assertSame(['baz', 'bar'], $response->getHeader('Foo'));
    }

    public function testCanConstructWithBody(): void
    {
        $response = new Response(200, [], 'baz');

        static::assertInstanceOf(StreamInterface::class, $response->getBody());
        static::assertSame('baz', (string) $response->getBody());
    }

    public function testNullBody(): void
    {
        $response = new Response(200, [], null);

        static::assertInstanceOf(StreamInterface::class, $response->getBody());
        static::assertSame('', (string) $response->getBody());
    }

    public function testFalseyBody(): void
    {
        $response = new Response(200, [], '0');

        static::assertInstanceOf(StreamInterface::class, $response->getBody());
        static::assertSame('0', (string) $response->getBody());
    }

    public function testWithStatusCodeAndNoReason(): void
    {
        $response = (new Response())->withStatus(201);

        static::assertSame(201, $response->getStatusCode());
        static::assertSame('Created', $response->getReasonPhrase());
    }

    public function testWithStatusCodeAndReason(): void
    {
        $response = (new Response())->withStatus(201, 'Foo');

        static::assertSame(201, $response->getStatusCode());
        static::assertSame('Foo', $response->getReasonPhrase());

        $response = (new Response())->withStatus(201, '0');

        static::assertSame(201, $response->getStatusCode());
        static::assertSame('0', $response->getReasonPhrase(), 'Falsey reason works');
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

        static::assertSame($response, $response->withProtocolVersion('1.1'));
    }

    public function testWithBody(): void
    {
        $body   = '0';
        $stream = \fopen('php://temp', 'r+b');

        \fwrite($stream, $body);
        \fseek($stream, 0);

        $response = (new Response())->withBody(new Stream($stream));

        static::assertInstanceOf(StreamInterface::class, $response->getBody());
        static::assertSame('0', (string) $response->getBody());
    }

    public function testSameInstanceWhenSameBody(): void
    {
        $response = new Response();
        $body     = $response->getBody();

        static::assertSame($response, $response->withBody($body));
    }

    public function testWithHeader(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('baZ', 'Bam');

        static::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        static::assertSame(['Foo' => ['Bar'], 'baZ' => ['Bam']], $response2->getHeaders());
        static::assertSame('Bam', $response2->getHeaderLine('baz'));
        static::assertSame(['Bam'], $response2->getHeader('baz'));
    }

    public function testWithHeaderAsArray(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('baZ', ['Bam', 'Bar']);

        static::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        static::assertSame(['Foo' => ['Bar'], 'baZ' => ['Bam', 'Bar']], $response2->getHeaders());
        static::assertSame('Bam,Bar', $response2->getHeaderLine('baz'));
        static::assertSame(['Bam', 'Bar'], $response2->getHeader('baz'));
    }

    public function testWithHeaderReplacesDifferentCase(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('foO', 'Bam');

        static::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        static::assertSame(['foO' => ['Bam']], $response2->getHeaders());
        static::assertSame('Bam', $response2->getHeaderLine('foo'));
        static::assertSame(['Bam'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeader(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('foO', 'Baz');

        static::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        static::assertSame(['Foo' => ['Bar', 'Baz']], $response2->getHeaders());
        static::assertSame('Bar,Baz', $response2->getHeaderLine('foo'));
        static::assertSame(['Bar', 'Baz'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeaderAsArray(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('foO', ['Baz', 'Bam']);

        static::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        static::assertSame(['Foo' => ['Bar', 'Baz', 'Bam']], $response2->getHeaders());
        static::assertSame('Bar,Baz,Bam', $response2->getHeaderLine('foo'));
        static::assertSame(['Bar', 'Baz', 'Bam'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeaderThatDoesNotExist(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('nEw', 'Baz');

        static::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        static::assertSame(['Foo' => ['Bar'], 'nEw' => ['Baz']], $response2->getHeaders());
        static::assertSame('Baz', $response2->getHeaderLine('new'));
        static::assertSame(['Baz'], $response2->getHeader('new'));
    }

    public function testWithoutHeaderThatExists(): void
    {
        $response  = new Response(200, ['Foo' => 'Bar', 'Baz' => 'Bam']);
        $response2 = $response->withoutHeader('foO');

        static::assertTrue($response->hasHeader('foo'));
        static::assertSame(['Foo' => ['Bar'], 'Baz' => ['Bam']], $response->getHeaders());
        static::assertFalse($response2->hasHeader('foo'));
        static::assertSame(['Baz' => ['Bam']], $response2->getHeaders());
    }

    public function testWithoutHeaderThatDoesNotExist(): void
    {
        $response  = new Response(200, ['Baz' => 'Bam']);
        $response2 = $response->withoutHeader('foO');

        static::assertSame($response, $response2);
        static::assertFalse($response2->hasHeader('foo'));
        static::assertSame(['Baz' => ['Bam']], $response2->getHeaders());
    }

    public function testSameInstanceWhenRemovingMissingHeader(): void
    {
        $response = new Response();

        static::assertSame($response, $response->withoutHeader('foo'));
    }

    public function testHeaderValuesAreTrimmed(): void
    {
        $response1 = new Response(200, ['OWS' => " \t \tFoo\t \t "]);
        $response2 = (new Response())->withHeader('OWS', " \t \tFoo\t \t ");
        $response3 = (new Response())->withAddedHeader('OWS', " \t \tFoo\t \t ");

        foreach ([$response1, $response2, $response3] as $response) {
            static::assertSame(['OWS' => ['Foo']], $response->getHeaders());
            static::assertSame('Foo', $response->getHeaderLine('OWS'));
            static::assertSame(['Foo'], $response->getHeader('OWS'));
        }
    }
}
