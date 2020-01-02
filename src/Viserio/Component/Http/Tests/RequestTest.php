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

namespace Viserio\Component\Http\Tests;

use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use stdClass;
use Viserio\Component\Http\Request;
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Stream\FnStream;
use Viserio\Component\Http\Uri;

/**
 * @internal
 *
 * @small
 *
 * @property \Psr\Http\Message\RequestInterface $classToTest
 */
final class RequestTest extends AbstractMessageTest
{
    /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface */
    private $uriMock;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->andReturn('');
        $uriMock->shouldReceive('getPath')
            ->andReturn('');
        $uriMock->shouldReceive('getQuery')
            ->andReturn('');

        $this->uriMock = $uriMock;

        $this->classToTest = new Request($this->uriMock);
    }

    public function testRequestImplementsInterface(): void
    {
        self::assertInstanceOf(RequestInterface::class, $this->classToTest);
    }

    public function testRequestTarget(): void
    {
        $message = $this->classToTest;
        $target = $message->getRequestTarget();

        self::assertIsString($target, 'getRequestTarget must return a string');
        self::assertEquals(
            '/',
            $target,
            'If no URI is available, and no request-target has been specifically provided, this method MUST return the string "/"'
        );

        $request = $this->classToTest->withRequestTarget('*');

        self::assertNotSame($this->classToTest, $request);
        self::assertEquals('*', $request->getRequestTarget());
    }

    public function testMethod(): void
    {
        $message = $this->classToTest;
        $target = $message->getMethod();

        self::assertIsString($target, 'getMethod must return a string');

        self::assertEquals('GET', $message->getMethod());

        $request = $message->withMethod('POST');

        self::assertNotSame($message, $request);
        self::assertEquals('POST', $request->getMethod());

        $request = $message->withMethod('head');
        self::assertEquals('head', $request->getMethod());
    }

    public function testValidDefaultUri(): void
    {
        $message = $this->classToTest;
        $body = $message->getUri();

        self::assertInstanceOf(
            UriInterface::class,
            $body,
            'getUri must return instance of Psr\Http\Message\UriInterface'
        );
    }

    /**
     * @dataProvider provideValidWithRequestTargetCases
     *
     * @param string $expectedRequestTarget
     */
    public function testValidWithRequestTarget($expectedRequestTarget): void
    {
        $request = $this->classToTest;
        $requestClone = clone $request;
        $newRequest = $request->withRequestTarget($expectedRequestTarget);

        $this->assertImmutable($requestClone, $request, $newRequest);
        self::assertEquals(
            $expectedRequestTarget,
            $newRequest->getRequestTarget(),
            'getRequestTarget does not match request target set in withRequestTarget'
        );
    }

    /**
     * @return iterable<array<string, string>>
     */
    public function provideValidWithRequestTargetCases(): iterable
    {
        yield [
            // Description => [request target],
            '*' => '*',
        ];
    }

    /**
     * @dataProvider provideValidWithMethodCases
     *
     * @param string $expectedMethod
     */
    public function testValidWithMethod($expectedMethod): void
    {
        $request = $this->classToTest;
        $requestClone = clone $request;
        $newRequest = $request->withMethod($expectedMethod);

        $this->assertImmutable($requestClone, $request, $newRequest);
        self::assertEquals(
            $expectedMethod,
            $newRequest->getMethod(),
            'getMethod does not match request target set in withMethod'
        );
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideValidWithMethodCases(): iterable
    {
        return [
            // Description => [request method],
            'GET' => ['GET'],
            'POST' => ['POST'],
            'PUT' => ['PUT'],
            'DELETE' => ['DELETE'],
            'PATCH' => ['PATCH'],
            'OPTIONS' => ['OPTIONS'],
        ];
    }

    public function testValidWithUri(): void
    {
        $request = $this->classToTest;
        $requestClone = clone $request;

        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class)
            ->shouldReceive('getHost')
            ->andReturn('')
            ->getMock();

        $newRequest = $request->withUri($uriMock);

        $this->assertImmutable($requestClone, $request, $newRequest);
        self::assertEquals(
            $uriMock,
            $newRequest->getUri(),
            'getUri does not match request target set in withUri'
        );
    }

    public function testConstructorDoesNotReadStreamBody(): void
    {
        $streamIsRead = false;

        /** @var resource $handler */
        $handler = \fopen('php://temp', 'r+b');

        $body = FnStream::decorate(new Stream($handler), [
            '__toString' => static function () use (&$streamIsRead): string {
                $streamIsRead = true;

                return '';
            },
        ]);

        $request = new Request('/', 'GET', [], $body);

        self::assertFalse($streamIsRead);
        self::assertSame($body, $request->getBody());
    }

    public function testEmptyRequestHostEmptyUriHostPreserveHostFalse(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = $this->getEmptyHostHeader()->withUri($uriMock);

        self::assertEquals('', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostEmptyUriHostPreserveHostTrue(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->andReturn('');

        $requestAfterUri = $this->getEmptyHostHeader()->withUri($uriMock, true);

        self::assertEquals('', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostDefaultUriHostPreserveHostFalse(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = (new Request($uriMock))->withUri($this->getDefaultUriHost());

        self::assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostDefaultUriHostPreserveHostTrue(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = (new Request($uriMock))->withUri($this->getDefaultUriHost(), true);

        self::assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostEmptyUriHostPreserveHostFalse(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = (new Request($uriMock))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($uriMock, false);

        self::assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostEmptyUriHostPreserveHostTrue(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = (new Request($uriMock))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($uriMock, true);

        self::assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostDefaultUriHostPreserveHostFalse(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = (new Request($uriMock))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), false);

        self::assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostDefaultUriHostPreserveHostTrue(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = (new Request($uriMock))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), true);

        self::assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testURIPortIsIgnoredIfHostIsEmpty(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = (new Request($uriMock))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), false);

        self::assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testURIPortIsUsedForBuildHostHeader(): void
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $uriMock */
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        /** @var \Psr\Http\Message\RequestInterface $request */
        $request = (new Request($uriMock))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHostAndPort(), false);

        self::assertEquals('baz.com:8080', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testHostHeaderSetFromUriOnCreationIfNoHostHeaderSpecified(): void
    {
        $request = new Request('http://www.example.com');

        self::assertTrue($request->hasHeader('Host'));
        self::assertEquals('www.example.com', $request->getHeaderLine('host'));
    }

    public function testHostHeaderNotSetFromUriOnCreationIfHostHeaderSpecified(): void
    {
        $request = new Request('http://www.example.com', null, ['Host' => 'www.test.com'], 'php://memory');

        self::assertEquals('www.test.com', $request->getHeaderLine('host'));
    }

    public function testRequestUriMayBeString(): void
    {
        $request = new Request('/', 'GET');

        self::assertEquals('/', (string) $request->getUri());
    }

    public function testRequestUriMayBeUri(): void
    {
        $uri = Uri::createFromString('/');
        $request = new Request($uri, 'GET');

        self::assertSame($uri, $request->getUri());
    }

    public function testValidateRequestUri(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URI: The submitted uri `///` is invalid for the following scheme(s): `http, https`');

        new Request('///', 'GET');
    }

    public function testWithNotValidMethodRequest(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported HTTP method [BOGUS METHOD].');

        new Request('/', 'BOGUS METHOD');
    }

    /**
     * @dataProvider provideAllowsCustomRequestMethodsThatFollowSpecCases
     *
     * @param mixed $method
     */
    public function testAllowsCustomRequestMethodsThatFollowSpec($method): void
    {
        $request = new Request(null, $method);
        self::assertSame($method, $request->getMethod());
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideAllowsCustomRequestMethodsThatFollowSpecCases(): iterable
    {
        return [
            // WebDAV methods
            'TRACE' => ['TRACE'],
            'PROPFIND' => ['PROPFIND'],
            'PROPPATCH' => ['PROPPATCH'],
            'MKCOL' => ['MKCOL'],
            'COPY' => ['COPY'],
            'MOVE' => ['MOVE'],
            'LOCK' => ['LOCK'],
            'UNLOCK' => ['UNLOCK'],
            // Arbitrary methods
            '#!ALPHA-1234&%' => ['#!ALPHA-1234&%'],
        ];
    }

    public function testCanConstructWithBody(): void
    {
        $request = new Request('/', 'GET', [], 'baz');

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertEquals('baz', (string) $request->getBody());
    }

    public function testNullBody(): void
    {
        $request = new Request('/', 'GET', [], null);

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertSame('', (string) $request->getBody());
    }

    public function testFalseyBody(): void
    {
        $request = new Request('/', 'GET', [], '0');

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertSame('0', (string) $request->getBody());
    }

    public function testWithUri(): void
    {
        $request1 = new Request('/', 'GET');
        $uri1 = $request1->getUri();

        $uri2 = Uri::createFromString('http://www.example.com');
        $request2 = $request1->withUri($uri2);

        self::assertNotSame($request1, $request2);
        self::assertSame($uri2, $request2->getUri());
        self::assertSame($uri1, $request1->getUri());
    }

    public function testSameInstanceWhenSameUri(): void
    {
        $request1 = new Request('http://foo.com', 'GET');
        $request2 = $request1->withUri($request1->getUri());

        self::assertSame($request1, $request2);
    }

    public function testWithRequestTarget(): void
    {
        $request1 = new Request('/', 'GET');
        $request2 = $request1->withRequestTarget('*');

        self::assertEquals('*', $request2->getRequestTarget());
        self::assertEquals('/', $request1->getRequestTarget());
    }

    public function testWithRequestNullUri(): void
    {
        $request = new Request(null, 'GET');

        self::assertEquals('/', $request->getRequestTarget());
    }

    public function testRequestToThrowException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URI provided; must be null, a string or a [\\Psr\\Http\\Message\\UriInterface] instance.');

        new Request(new stdClass(), 'GET');
    }

    public function testRequestTargetDoesNotAllowSpaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request target provided; cannot contain whitespace');

        $request1 = new Request('/', 'GET');
        $request1->withRequestTarget('/foo bar');
    }

    public function testRequestTargetDefaultsToSlash(): void
    {
        $request1 = new Request('', 'GET');

        self::assertEquals('/', $request1->getRequestTarget());

        $request2 = new Request('*', 'GET');

        self::assertEquals('*', $request2->getRequestTarget());

        $request3 = new Request('http://foo.com/bar baz/', 'GET');

        self::assertEquals('/bar%20baz/', $request3->getRequestTarget());
    }

    public function testBuildsRequestTarget(): void
    {
        $request1 = new Request('http://foo.com/baz?bar=bam', 'GET');

        self::assertEquals('/baz?bar=bam', $request1->getRequestTarget());
    }

    public function testBuildsRequestTargetWithFalseyQuery(): void
    {
        $request1 = new Request('http://foo.com/baz?0', 'GET');

        self::assertEquals('/baz?0', $request1->getRequestTarget());
    }

    public function testHostIsAddedFirst(): void
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', ['Foo' => 'Bar']);

        self::assertEquals([
            'Host' => ['foo.com'],
            'Foo' => ['Bar'],
        ], $request->getHeaders());
    }

    public function testCanGetHeaderAsCsv(): void
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', [
            'Foo' => ['a', 'b', 'c'],
        ]);

        self::assertEquals('a,b,c', $request->getHeaderLine('Foo'));
        self::assertEquals('', $request->getHeaderLine('Bar'));
    }

    public function testHostIsNotOverwrittenWhenPreservingHost(): void
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', ['Host' => 'a.com']);

        self::assertEquals(['Host' => ['a.com']], $request->getHeaders());

        $request2 = $request->withUri(Uri::createFromString('http://www.foo.com/bar'), true);

        self::assertEquals('a.com', $request2->getHeaderLine('Host'));
    }

    public function testOverridesHostWithUri(): void
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET');

        self::assertEquals(['Host' => ['foo.com']], $request->getHeaders());

        $request2 = $request->withUri(Uri::createFromString('http://www.baz.com/bar'));

        self::assertEquals('www.baz.com', $request2->getHeaderLine('Host'));
    }

    public function testAddsPortToHeader(): void
    {
        $request = new Request('http://foo.com:8124/bar', 'GET');

        self::assertEquals('foo.com:8124', $request->getHeaderLine('host'));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function provideWithUriAndNoPreserveHostWillOverwriteHostHeaderRegardlessOfOriginalCaseCases(): iterable
    {
        return [
            'lowercase' => ['host'],
            'mixed-4' => ['hosT'],
            'mixed-3-4' => ['hoST'],
            'reverse-titlecase' => ['hOST'],
            'uppercase' => ['HOST'],
            'mixed-1-2-3' => ['HOSt'],
            'mixed-1-2' => ['HOst'],
            'titlecase' => ['Host'],
            'mixed-1-4' => ['HosT'],
            'mixed-1-2-4' => ['HOsT'],
            'mixed-1-3-4' => ['HoST'],
            'mixed-1-3' => ['HoSt'],
            'mixed-2-3' => ['hOSt'],
            'mixed-2-4' => ['hOsT'],
            'mixed-2' => ['hOst'],
            'mixed-3' => ['hoSt'],
        ];
    }

    /**
     * @dataProvider provideWithUriAndNoPreserveHostWillOverwriteHostHeaderRegardlessOfOriginalCaseCases
     *
     * @param string $hostKey
     */
    public function testWithUriAndNoPreserveHostWillOverwriteHostHeaderRegardlessOfOriginalCase($hostKey): void
    {
        $request = (new Request('/'))->withHeader($hostKey, 'example.com');
        $uri = Uri::createFromString('http://example.org/foo/bar');
        /** @var \Viserio\Component\Http\Request $new */
        $new = $request->withUri($uri);
        $host = $new->getHeaderLine('host');

        self::assertEquals('example.org', $host);

        $headers = $new->getHeaders();

        self::assertArrayHasKey('Host', $headers);

        if ($hostKey !== 'Host') {
            self::assertArrayNotHasKey($hostKey, $headers);
        }
    }

    public function testAddsPortToHeaderAndReplacePreviousPort(): void
    {
        $request = new Request('http://foo.com:8124/bar', 'GET');
        $request = $request->withUri(Uri::createFromString('http://foo.com:8125/bar'));

        self::assertEquals('foo.com:8125', $request->getHeaderLine('host'));
    }

    /**
     * @return \Psr\Http\Message\RequestInterface
     */
    private function getEmptyHostHeader(): RequestInterface
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $emptyHostHeaderUriMock */
        $emptyHostHeaderUriMock = $this->mock(UriInterface::class);
        $emptyHostHeaderUriMock->shouldReceive('getHost')
            ->andReturn('');

        return new Request($emptyHostHeaderUriMock);
    }

    /**
     * @return \Mockery\MockInterface|\Psr\Http\Message\UriInterface
     */
    private function getDefaultUriHost()
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $defaultUriHostMock */
        $defaultUriHostMock = $this->mock(UriInterface::class);
        $defaultUriHostMock->shouldReceive('getHost')
            ->andReturn('baz.com');
        $defaultUriHostMock->shouldReceive('getPort')
            ->andReturn(null);

        return $defaultUriHostMock;
    }

    /**
     * @return \Mockery\MockInterface|\Psr\Http\Message\UriInterface
     */
    private function getDefaultUriHostAndPort()
    {
        /** @var \Mockery\MockInterface|\Psr\Http\Message\UriInterface $defaultUriHostAndPortMock */
        $defaultUriHostAndPortMock = $this->mock(UriInterface::class);
        $defaultUriHostAndPortMock->shouldReceive('getHost')
            ->andReturn('baz.com');
        $defaultUriHostAndPortMock->shouldReceive('getPort')
            ->andReturn('8080');

        return $defaultUriHostAndPortMock;
    }
}
