<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests;

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
 */
final class RequestTest extends AbstractMessageTest
{
    private $mockUri;

    protected function setUp(): void
    {
        parent::setUp();

        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->andReturn('');
        $uri->shouldReceive('getPath')
            ->andReturn('');
        $uri->shouldReceive('getQuery')
            ->andReturn('');

        $this->mockUri = $uri;

        $this->classToTest = new Request($this->mockUri);
    }

    public function testRequestImplementsInterface(): void
    {
        $this->assertInstanceOf(RequestInterface::class, $this->classToTest);
    }

    public function testValidDefaultRequestTarget(): void
    {
        $message = $this->classToTest;
        $target  = $message->getRequestTarget();

        $this->assertIsString($target, 'getRequestTarget must return a string');
        $this->assertEquals(
            '/',
            $target,
            'If no URI is available, and no request-target has been specifically provided, this method MUST return the string "/"'
        );
    }

    public function testValidDefaultMethod(): void
    {
        $message = $this->classToTest;
        $target  = $message->getMethod();

        $this->assertIsString($target, 'getMethod must return a string');
    }

    public function testValidDefaultUri(): void
    {
        $message = $this->classToTest;
        $body    = $message->getUri();

        $this->assertInstanceOf(
            UriInterface::class,
            $body,
            'getUri must return instance of Psr\Http\Message\UriInterface'
        );
    }

    /**
     * @dataProvider validRequestTargetProvider
     *
     * @param string $expectedRequestTarget
     */
    public function testValidWithRequestTarget($expectedRequestTarget): void
    {
        $request      = $this->classToTest;
        $requestClone = clone $request;
        $newRequest   = $request->withRequestTarget($expectedRequestTarget);

        $this->assertImmutable($requestClone, $request, $newRequest);
        $this->assertEquals(
            $expectedRequestTarget,
            $newRequest->getRequestTarget(),
            'getRequestTarget does not match request target set in withRequestTarget'
        );
    }

    public function validRequestTargetProvider()
    {
        return [
            // Description => [request target],
            '*' => ['*'],
        ];
    }

    /**
     * @dataProvider validMethodProvider
     *
     * @param string $expectedMethod
     */
    public function testValidWithMethod($expectedMethod): void
    {
        $request      = $this->classToTest;
        $requestClone = clone $request;
        $newRequest   = $request->withMethod($expectedMethod);

        $this->assertImmutable($requestClone, $request, $newRequest);
        $this->assertEquals(
            $expectedMethod,
            $newRequest->getMethod(),
            'getMethod does not match request target set in withMethod'
        );
    }

    public function validMethodProvider()
    {
        return [
            // Description => [request method],
            'GET'     => ['GET'],
            'POST'    => ['POST'],
            'PUT'     => ['PUT'],
            'DELETE'  => ['DELETE'],
            'PATCH'   => ['PATCH'],
            'OPTIONS' => ['OPTIONS'],
        ];
    }

    public function testValidWithUri(): void
    {
        $request      = $this->classToTest;
        $requestClone = clone $request;

        $uri = $this->mock(UriInterface::class)
            ->shouldReceive('getHost')
            ->andReturn('')
            ->getMock();
        $newRequest = $request->withUri($uri);

        $this->assertImmutable($requestClone, $request, $newRequest);
        $this->assertEquals(
            $uri,
            $newRequest->getUri(),
            'getUri does not match request target set in withUri'
        );
    }

    public function testConstructorDoesNotReadStreamBody(): void
    {
        $streamIsRead = false;

        $body = FnStream::decorate(new Stream(\fopen('php://temp', 'r+b')), [
            '__toString' => function () use (&$streamIsRead) {
                $streamIsRead = true;

                return '';
            },
        ]);

        $request = new Request('/', 'GET', [], $body);

        $this->assertFalse($streamIsRead);
        $this->assertSame($body, $request->getBody());
    }

    public function testEmptyRequestHostEmptyUriHostPreserveHostFalse(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $requestAfterUri = $this->getEmptyHostHeader()->withUri($uri);

        $this->assertEquals('', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostEmptyUriHostPreserveHostTrue(): void
    {
        $uriMock = $this->mock(UriInterface::class);
        $uriMock->shouldReceive('getHost')
            ->andReturn('');
        $requestAfterUri = $this->getEmptyHostHeader()->withUri($uriMock, true);

        $this->assertEquals('', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostDefaultUriHostPreserveHostFalse(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = (new Request($uri))->withUri($this->getDefaultUriHost());

        $this->assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostDefaultUriHostPreserveHostTrue(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = (new Request($uri))->withUri($this->getDefaultUriHost());

        $this->assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostEmptyUriHostPreserveHostFalse(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        /** @var Request $request */
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($uri, false);

        $this->assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostEmptyUriHostPreserveHostTrue(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        /** @var Request $request */
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($uri, true);

        $this->assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostDefaultUriHostPreserveHostFalse(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), false);

        $this->assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostDefaultUriHostPreserveHostTrue(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), true);

        $this->assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testURIPortIsIgnoredIfHostIsEmpty(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once();
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), false);

        $this->assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testURIPortIsUsedForBuildHostHeader(): void
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHostAndPort(), false);

        $this->assertEquals('baz.com:8080', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testHostHeaderSetFromUriOnCreationIfNoHostHeaderSpecified(): void
    {
        $request = new Request('http://www.example.com');

        $this->assertTrue($request->hasHeader('Host'));
        $this->assertEquals('www.example.com', $request->getHeaderLine('host'));
    }

    public function testHostHeaderNotSetFromUriOnCreationIfHostHeaderSpecified(): void
    {
        $request = new Request('http://www.example.com', null, ['Host' => 'www.test.com'], 'php://memory');

        $this->assertEquals('www.test.com', $request->getHeaderLine('host'));
    }

    public function testRequestUriMayBeString(): void
    {
        $request = new Request('/', 'GET');

        $this->assertEquals('/', (string) $request->getUri());
    }

    public function testRequestUriMayBeUri(): void
    {
        $uri     = Uri::createFromString('/');
        $request = new Request($uri, 'GET');

        $this->assertSame($uri, $request->getUri());
    }

    public function testValidateRequestUri(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URI: The submitted uri `///` is invalid for the following scheme(s): `http, https`');

        new Request('///', 'GET');
    }

    public function testWithNotValidMethodRequest(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported HTTP method [BOGUS METHOD].');

        new Request('/', 'BOGUS METHOD');
    }

    /**
     * @dataProvider customRequestMethods
     *
     * @param mixed $method
     */
    public function testAllowsCustomRequestMethodsThatFollowSpec($method): void
    {
        $request = new Request(null, $method);
        $this->assertSame($method, $request->getMethod());
    }

    public function customRequestMethods()
    {
        return[
            // WebDAV methods
            'TRACE'     => ['TRACE'],
            'PROPFIND'  => ['PROPFIND'],
            'PROPPATCH' => ['PROPPATCH'],
            'MKCOL'     => ['MKCOL'],
            'COPY'      => ['COPY'],
            'MOVE'      => ['MOVE'],
            'LOCK'      => ['LOCK'],
            'UNLOCK'    => ['UNLOCK'],
            // Arbitrary methods
            '#!ALPHA-1234&%' => ['#!ALPHA-1234&%'],
        ];
    }

    public function testCanConstructWithBody(): void
    {
        $request = new Request('/', 'GET', [], 'baz');

        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertEquals('baz', (string) $request->getBody());
    }

    public function testNullBody(): void
    {
        $request = new Request('/', 'GET', [], null);

        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('', (string) $request->getBody());
    }

    public function testFalseyBody(): void
    {
        $request = new Request('/', 'GET', [], '0');

        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('0', (string) $request->getBody());
    }

    public function testWithUri(): void
    {
        $request1 = new Request('/', 'GET');
        $uri1     = $request1->getUri();

        $uri2     = Uri::createFromString('http://www.example.com');
        $request2 = $request1->withUri($uri2);

        $this->assertNotSame($request1, $request2);
        $this->assertSame($uri2, $request2->getUri());
        $this->assertSame($uri1, $request1->getUri());
    }

    public function testSameInstanceWhenSameUri(): void
    {
        $request1 = new Request('http://foo.com', 'GET');
        $request2 = $request1->withUri($request1->getUri());

        $this->assertSame($request1, $request2);
    }

    public function testWithRequestTarget(): void
    {
        $request1 = new Request('/', 'GET');
        $request2 = $request1->withRequestTarget('*');

        $this->assertEquals('*', $request2->getRequestTarget());
        $this->assertEquals('/', $request1->getRequestTarget());
    }

    public function testWithRequestNullUri(): void
    {
        $request = new Request(null, 'GET');

        $this->assertEquals('/', $request->getRequestTarget());
    }

    public function testRequestToThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid URI provided; must be null, a string or a [\\Psr\\Http\\Message\\UriInterface] instance.');

        new Request(new stdClass(), 'GET');
    }

    public function testRequestTargetDoesNotAllowSpaces(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid request target provided; cannot contain whitespace');

        $request1 = new Request('/', 'GET');
        $request1->withRequestTarget('/foo bar');
    }

    public function testRequestTargetDefaultsToSlash(): void
    {
        $request1 = new Request('', 'GET');

        $this->assertEquals('/', $request1->getRequestTarget());

        $request2 = new Request('*', 'GET');

        $this->assertEquals('*', $request2->getRequestTarget());

        $request3 = new Request('http://foo.com/bar baz/', 'GET');

        $this->assertEquals('/bar%20baz/', $request3->getRequestTarget());
    }

    public function testBuildsRequestTarget(): void
    {
        $request1 = new Request('http://foo.com/baz?bar=bam', 'GET');

        $this->assertEquals('/baz?bar=bam', $request1->getRequestTarget());
    }

    public function testBuildsRequestTargetWithFalseyQuery(): void
    {
        $request1 = new Request('http://foo.com/baz?0', 'GET');

        $this->assertEquals('/baz?0', $request1->getRequestTarget());
    }

    public function testHostIsAddedFirst(): void
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', ['Foo' => 'Bar']);

        $this->assertEquals([
            'Host' => ['foo.com'],
            'Foo'  => ['Bar'],
        ], $request->getHeaders());
    }

    public function testCanGetHeaderAsCsv(): void
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', [
            'Foo' => ['a', 'b', 'c'],
        ]);

        $this->assertEquals('a,b,c', $request->getHeaderLine('Foo'));
        $this->assertEquals('', $request->getHeaderLine('Bar'));
    }

    public function testHostIsNotOverwrittenWhenPreservingHost(): void
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', ['Host' => 'a.com']);

        $this->assertEquals(['Host' => ['a.com']], $request->getHeaders());

        $request2 = $request->withUri(Uri::createFromString('http://www.foo.com/bar'), true);

        $this->assertEquals('a.com', $request2->getHeaderLine('Host'));
    }

    public function testOverridesHostWithUri(): void
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET');

        $this->assertEquals(['Host' => ['foo.com']], $request->getHeaders());

        $request2 = $request->withUri(Uri::createFromString('http://www.baz.com/bar'));

        $this->assertEquals('www.baz.com', $request2->getHeaderLine('Host'));
    }

    public function testAddsPortToHeader(): void
    {
        $request = new Request('http://foo.com:8124/bar', 'GET');

        $this->assertEquals('foo.com:8124', $request->getHeaderLine('host'));
    }

    /**
     * @return array
     */
    public function hostHeaderKeys(): array
    {
        return [
            'lowercase'            => ['host'],
            'mixed-4'              => ['hosT'],
            'mixed-3-4'            => ['hoST'],
            'reverse-titlecase'    => ['hOST'],
            'uppercase'            => ['HOST'],
            'mixed-1-2-3'          => ['HOSt'],
            'mixed-1-2'            => ['HOst'],
            'titlecase'            => ['Host'],
            'mixed-1-4'            => ['HosT'],
            'mixed-1-2-4'          => ['HOsT'],
            'mixed-1-3-4'          => ['HoST'],
            'mixed-1-3'            => ['HoSt'],
            'mixed-2-3'            => ['hOSt'],
            'mixed-2-4'            => ['hOsT'],
            'mixed-2'              => ['hOst'],
            'mixed-3'              => ['hoSt'],
        ];
    }

    /**
     * @dataProvider hostHeaderKeys
     *
     * @param string $hostKey
     */
    public function testWithUriAndNoPreserveHostWillOverwriteHostHeaderRegardlessOfOriginalCase($hostKey): void
    {
        $request = (new Request('/'))->withHeader($hostKey, 'example.com');
        $uri     = Uri::createFromString('http://example.org/foo/bar');
        /** @var \Viserio\Component\Http\Request $new */
        $new  = $request->withUri($uri);
        $host = $new->getHeaderLine('host');

        $this->assertEquals('example.org', $host);

        $headers = $new->getHeaders();

        $this->assertArrayHasKey('Host', $headers);

        if ($hostKey !== 'Host') {
            $this->assertArrayNotHasKey($hostKey, $headers);
        }
    }

    public function testAddsPortToHeaderAndReplacePreviousPort(): void
    {
        $request = new Request('http://foo.com:8124/bar', 'GET');
        $request = $request->withUri(Uri::createFromString('http://foo.com:8125/bar'));

        $this->assertEquals('foo.com:8125', $request->getHeaderLine('host'));
    }

    private function getEmptyHostHeader()
    {
        $emptyHostHeaderMockUri = $this->mock(UriInterface::class);
        $emptyHostHeaderMockUri->shouldReceive('getHost')
            ->andReturn('');

        return new Request($emptyHostHeaderMockUri);
    }

    private function getDefaultUriHost()
    {
        $defaultUriHost = $this->mock(UriInterface::class);
        $defaultUriHost->shouldReceive('getHost')
            ->andReturn('baz.com');
        $defaultUriHost->shouldReceive('getPort')
            ->andReturn(null);

        return $defaultUriHost;
    }

    private function getDefaultUriHostAndPort()
    {
        $defaultUriHostAndPort = $this->mock(UriInterface::class);
        $defaultUriHostAndPort->shouldReceive('getHost')
            ->andReturn('baz.com');
        $defaultUriHostAndPort->shouldReceive('getPort')
            ->andReturn('8080');

        return $defaultUriHostAndPort;
    }
}
