<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;

class ResponseTest extends AbstractMessageTest
{
    public function setUP()
    {
        $this->classToTest = new Response();
    }

    public function testResponseImplementsInterface()
    {
        self::assertInstanceOf(ResponseInterface::class, $this->classToTest);
    }

    public function testValidDefaultStatusCode()
    {
        $message    = $this->classToTest;
        $statusCode = $message->getStatusCode();

        self::assertInternalType('integer', $statusCode, 'getStatusCode must return an integer');
    }

    public function testValidDefaultReasonPhrase()
    {
        $message      = $this->classToTest;
        $reasonPhrase = $message->getReasonPhrase();

        self::assertInternalType('string', $reasonPhrase, 'getReasonPhrase must return a string');
    }

    // Test methods for change instances status
    public function testValidWithStatusDefaultReasonPhrase()
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $statusCode   = 100;
        $newMessage   = $message->withStatus($statusCode);

        self::assertImmutable($messageClone, $message, $newMessage);
        self::assertEquals(
            $statusCode,
            $newMessage->getStatusCode(),
            'getStatusCode does not match code set in withStatus'
        );
    }

    public function testValidWithStatusCustomReasonPhrase()
    {
        $message      = $this->classToTest;
        $messageClone = clone $message;
        $statusCode   = 100;
        $reasonPhrase = 'example';
        $newMessage   = $message->withStatus($statusCode, $reasonPhrase);

        self::assertImmutable($messageClone, $message, $newMessage);
        self::assertEquals(
            $statusCode,
            $newMessage->getStatusCode(),
            'getStatusCode does not match code set in withStatus'
        );
        self::assertEquals(
            $reasonPhrase,
            $newMessage->getReasonPhrase(),
            'getReasonPhrase does not match code set in withStatus'
        );
    }

    public function testDefaultConstructor()
    {
        $response = $this->classToTest;

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('1.1', $response->getProtocolVersion());
        self::assertSame('OK', $response->getReasonPhrase());
        self::assertSame([], $response->getHeaders());
        self::assertInstanceOf(StreamInterface::class, $response->getBody());
        self::assertSame('', (string) $response->getBody());
    }

    public function testCanConstructWithStatusCode()
    {
        $response = new Response(404);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Not Found', $response->getReasonPhrase());
    }

    public function testConstructorDoesNotReadStreamBody()
    {
        $streamIsRead = false;
        $body         = FnStream::decorate(new Stream(fopen('php://temp', 'r+')), [
            '__toString' => function () use (&$streamIsRead) {
                $streamIsRead = true;

                return '';
            },
        ]);

        $response = new Response(200, [], $body);

        self::assertFalse($streamIsRead);
        self::assertSame($body, $response->getBody());
    }

    public function testCanConstructWithHeaders()
    {
        $response = new Response(200, ['Foo' => 'Bar']);

        self::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        self::assertSame('Bar', $response->getHeaderLine('Foo'));
        self::assertSame(['Bar'], $response->getHeader('Foo'));
    }

    public function testCanConstructWithHeadersAsArray()
    {
        $response = new Response(200, [
            'Foo' => ['baz', 'bar'],
        ]);

        self::assertSame(['Foo' => ['baz', 'bar']], $response->getHeaders());
        self::assertSame('baz,bar', $response->getHeaderLine('Foo'));
        self::assertSame(['baz', 'bar'], $response->getHeader('Foo'));
    }

    public function testCanConstructWithBody()
    {
        $response = new Response(200, [], 'baz');

        self::assertInstanceOf(StreamInterface::class, $response->getBody());
        self::assertSame('baz', (string) $response->getBody());
    }

    public function testNullBody()
    {
        $response = new Response(200, [], null);

        self::assertInstanceOf(StreamInterface::class, $response->getBody());
        self::assertSame('', (string) $response->getBody());
    }

    public function testFalseyBody()
    {
        $response = new Response(200, [], '0');

        self::assertInstanceOf(StreamInterface::class, $response->getBody());
        self::assertSame('0', (string) $response->getBody());
    }

    public function testWithStatusCodeAndNoReason()
    {
        $response = (new Response())->withStatus(201);

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('Created', $response->getReasonPhrase());
    }

    public function testWithStatusCodeAndReason()
    {
        $response = (new Response())->withStatus(201, 'Foo');

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('Foo', $response->getReasonPhrase());

        $response = (new Response())->withStatus(201, '0');

        self::assertSame(201, $response->getStatusCode());
        self::assertSame('0', $response->getReasonPhrase(), 'Falsey reason works');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid HTTP version. Must be one of: 1.0, 1.1, 2.0
     */
    public function testWithProtocolVersion()
    {
        $response = (new Response())->withProtocolVersion('1000');
    }

    public function testSameInstanceWhenSameProtocol()
    {
        $response = new Response();

        self::assertSame($response, $response->withProtocolVersion('1.1'));
    }

    public function testWithBody()
    {
        $body   = '0';
        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $body);
        fseek($stream, 0);

        $response = (new Response())->withBody(new Stream($stream));

        self::assertInstanceOf(StreamInterface::class, $response->getBody());
        self::assertSame('0', (string) $response->getBody());
    }

    public function testSameInstanceWhenSameBody()
    {
        $response = new Response();
        $body     = $response->getBody();

        self::assertSame($response, $response->withBody($body));
    }

    public function testWithHeader()
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('baZ', 'Bam');

        self::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        self::assertSame(['Foo' => ['Bar'], 'baZ' => ['Bam']], $response2->getHeaders());
        self::assertSame('Bam', $response2->getHeaderLine('baz'));
        self::assertSame(['Bam'], $response2->getHeader('baz'));
    }

    public function testWithHeaderAsArray()
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('baZ', ['Bam', 'Bar']);

        self::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        self::assertSame(['Foo' => ['Bar'], 'baZ' => ['Bam', 'Bar']], $response2->getHeaders());
        self::assertSame('Bam,Bar', $response2->getHeaderLine('baz'));
        self::assertSame(['Bam', 'Bar'], $response2->getHeader('baz'));
    }

    public function testWithHeaderReplacesDifferentCase()
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withHeader('foO', 'Bam');

        self::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        self::assertSame(['foO' => ['Bam']], $response2->getHeaders());
        self::assertSame('Bam', $response2->getHeaderLine('foo'));
        self::assertSame(['Bam'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeader()
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('foO', 'Baz');

        self::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        self::assertSame(['Foo' => ['Bar', 'Baz']], $response2->getHeaders());
        self::assertSame('Bar,Baz', $response2->getHeaderLine('foo'));
        self::assertSame(['Bar', 'Baz'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeaderAsArray()
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('foO', ['Baz', 'Bam']);

        self::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        self::assertSame(['Foo' => ['Bar', 'Baz', 'Bam']], $response2->getHeaders());
        self::assertSame('Bar,Baz,Bam', $response2->getHeaderLine('foo'));
        self::assertSame(['Bar', 'Baz', 'Bam'], $response2->getHeader('foo'));
    }

    public function testWithAddedHeaderThatDoesNotExist()
    {
        $response  = new Response(200, ['Foo' => 'Bar']);
        $response2 = $response->withAddedHeader('nEw', 'Baz');

        self::assertSame(['Foo' => ['Bar']], $response->getHeaders());
        self::assertSame(['Foo' => ['Bar'], 'nEw' => ['Baz']], $response2->getHeaders());
        self::assertSame('Baz', $response2->getHeaderLine('new'));
        self::assertSame(['Baz'], $response2->getHeader('new'));
    }

    public function testWithoutHeaderThatExists()
    {
        $response  = new Response(200, ['Foo' => 'Bar', 'Baz' => 'Bam']);
        $response2 = $response->withoutHeader('foO');

        self::assertTrue($response->hasHeader('foo'));
        self::assertSame(['Foo' => ['Bar'], 'Baz' => ['Bam']], $response->getHeaders());
        self::assertFalse($response2->hasHeader('foo'));
        self::assertSame(['Baz' => ['Bam']], $response2->getHeaders());
    }

    public function testWithoutHeaderThatDoesNotExist()
    {
        $response  = new Response(200, ['Baz' => 'Bam']);
        $response2 = $response->withoutHeader('foO');

        self::assertSame($response, $response2);
        self::assertFalse($response2->hasHeader('foo'));
        self::assertSame(['Baz' => ['Bam']], $response2->getHeaders());
    }

    public function testSameInstanceWhenRemovingMissingHeader()
    {
        $response = new Response();

        self::assertSame($response, $response->withoutHeader('foo'));
    }

    public function testHeaderValuesAreTrimmed()
    {
        $response1 = new Response(200, ['OWS' => " \t \tFoo\t \t "]);
        $response2 = (new Response())->withHeader('OWS', " \t \tFoo\t \t ");
        $response3 = (new Response())->withAddedHeader('OWS', " \t \tFoo\t \t ");

        foreach ([$response1, $response2, $response3] as $response) {
            self::assertSame(['OWS' => ['Foo']], $response->getHeaders());
            self::assertSame('Foo', $response->getHeaderLine('OWS'));
            self::assertSame(['Foo'], $response->getHeader('OWS'));
        }
    }
}
