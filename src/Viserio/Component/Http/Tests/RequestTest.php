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

class RequestTest extends AbstractMessageTest
{
    private $mockUri;

    public function setUp()
    {
        parent::setUp();

        $this->mockUri = $this->mock(UriInterface::class)
            ->shouldReceive('getHost')
            ->andReturn('')
            ->shouldReceive('getPath')
            ->andReturn('')
            ->shouldReceive('getQuery')
            ->andReturn('')
            ->getMock();
        $this->classToTest = new Request($this->mockUri);
    }

    public function testRequestImplementsInterface()
    {
        self::assertInstanceOf(RequestInterface::class, $this->classToTest);
    }

    public function testValidDefaultRequestTarget()
    {
        $message = $this->classToTest;
        $target  = $message->getRequestTarget();

        self::assertInternalType('string', $target, 'getRequestTarget must return a string');
        self::assertEquals(
            '/',
            $target,
            'If no URI is available, and no request-target has been specifically provided, this method MUST return the string "/"'
        );
    }

    public function testValidDefaultMethod()
    {
        $message = $this->classToTest;
        $target  = $message->getMethod();

        self::assertInternalType('string', $target, 'getMethod must return a string');
    }

    public function testValidDefaultUri()
    {
        $message = $this->classToTest;
        $body    = $message->getUri();

        self::assertInstanceOf(
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
    public function testValidWithRequestTarget($expectedRequestTarget)
    {
        $request      = $this->classToTest;
        $requestClone = clone $request;
        $newRequest   = $request->withRequestTarget($expectedRequestTarget);

        self::assertImmutable($requestClone, $request, $newRequest);
        self::assertEquals(
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
    public function testValidWithMethod($expectedMethod)
    {
        $request      = $this->classToTest;
        $requestClone = clone $request;
        $newRequest   = $request->withMethod($expectedMethod);

        self::assertImmutable($requestClone, $request, $newRequest);
        self::assertEquals(
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

    public function testValidWithUri()
    {
        $request      = $this->classToTest;
        $requestClone = clone $request;

        $uri = $this->mock(UriInterface::class)
            ->shouldReceive('getHost')
            ->andReturn('')
            ->getMock();
        $newRequest = $request->withUri($uri);

        self::assertImmutable($requestClone, $request, $newRequest);
        self::assertEquals(
            $uri,
            $newRequest->getUri(),
            'getUri does not match request target set in withUri'
        );
    }

    public function testConstructorDoesNotReadStreamBody()
    {
        $streamIsRead = false;

        $body = FnStream::decorate(new Stream(fopen('php://temp', 'r+')), [
            '__toString' => function () use (&$streamIsRead) {
                $streamIsRead = true;

                return '';
            },
        ]);

        $request = new Request('/', 'GET', [], $body);

        self::assertFalse($streamIsRead);
        self::assertSame($body, $request->getBody());
    }

    public function testEmptyRequestHostEmptyUriHostPreserveHostFalse()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $requestAfterUri = $this->getEmptyHostHeader()->withUri($uri, false);

        self::assertEquals('', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostEmptyUriHostPreserveHostTrue()
    {
        $requestAfterUri = $this->getEmptyHostHeader()->withUri($this->mock(UriInterface::class), true);

        self::assertEquals('', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostDefaultUriHostPreserveHostFalse()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = (new Request($uri))->withUri($this->getDefaultUriHost(), false);

        self::assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostDefaultUriHostPreserveHostTrue()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = (new Request($uri))->withUri($this->getDefaultUriHost(), false);

        self::assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostEmptyUriHostPreserveHostFalse()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($uri, false);

        self::assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostEmptyUriHostPreserveHostTrue()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($uri, true);

        self::assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostDefaultUriHostPreserveHostFalse()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), false);

        self::assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostDefaultUriHostPreserveHostTrue()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), true);

        self::assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testURIPortIsIgnoredIfHostIsEmpty()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once();
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), false);

        self::assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testURIPortIsUsedForBuildHostHeader()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request         = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHostAndPort(), false);

        self::assertEquals('baz.com:8080', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testHostHeaderSetFromUriOnCreationIfNoHostHeaderSpecified()
    {
        $request = new Request('http://www.example.com');

        self::assertTrue($request->hasHeader('Host'));
        self::assertEquals('www.example.com', $request->getHeaderLine('host'));
    }

    public function testHostHeaderNotSetFromUriOnCreationIfHostHeaderSpecified()
    {
        $request = new Request('http://www.example.com', null, ['Host' => 'www.test.com'], 'php://memory');

        self::assertEquals('www.test.com', $request->getHeaderLine('host'));
    }

    public function testRequestUriMayBeString()
    {
        $request = new Request('/', 'GET');

        self::assertEquals('/', (string) $request->getUri());
    }

    public function testRequestUriMayBeUri()
    {
        $uri     = new Uri('/');
        $request = new Request($uri, 'GET');

        self::assertSame($uri, $request->getUri());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid URI: The submitted uri `///` is invalid for the following scheme(s): `http, https`
     */
    public function testValidateRequestUri()
    {
        new Request('///', 'GET');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unsupported HTTP method "FOO".
     */
    public function testWithNotValidMethodRequest()
    {
        new Request('/', 'foo');
    }

    public function testCanConstructWithBody()
    {
        $request = new Request('/', 'GET', [], 'baz');

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertEquals('baz', (string) $request->getBody());
    }

    public function testNullBody()
    {
        $request = new Request('/', 'GET', [], null);

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertSame('', (string) $request->getBody());
    }

    public function testFalseyBody()
    {
        $request = new Request('/', 'GET', [], '0');

        self::assertInstanceOf(StreamInterface::class, $request->getBody());
        self::assertSame('0', (string) $request->getBody());
    }

    public function testCapitalizesMethod()
    {
        $request = new Request('/', 'get');

        self::assertEquals('GET', $request->getMethod());
    }

    public function testCapitalizesWithMethod()
    {
        $request = new Request('/', 'GET');

        self::assertEquals('PUT', $request->withMethod('put')->getMethod());
    }

    public function testWithUri()
    {
        $request1 = new Request('/', 'GET');
        $uri1     = $request1->getUri();

        $uri2     = new Uri('http://www.example.com');
        $request2 = $request1->withUri($uri2);

        self::assertNotSame($request1, $request2);
        self::assertSame($uri2, $request2->getUri());
        self::assertSame($uri1, $request1->getUri());
    }

    public function testSameInstanceWhenSameUri()
    {
        $request1 = new Request('http://foo.com', 'GET');
        $request2 = $request1->withUri($request1->getUri());

        self::assertSame($request1, $request2);
    }

    public function testWithRequestTarget()
    {
        $request1 = new Request('/', 'GET');
        $request2 = $request1->withRequestTarget('*');

        self::assertEquals('*', $request2->getRequestTarget());
        self::assertEquals('/', $request1->getRequestTarget());
    }

    public function testWithRequestNullUri()
    {
        $request = new Request(null, 'GET');

        self::assertEquals('/', $request->getRequestTarget());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid URI provided; must be null, a string, or a Psr\Http\Message\UriInterface instance.
     */
    public function testRequestToThrowException()
    {
        new Request(new stdClass(), 'GET');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid request target provided; cannot contain whitespace
     */
    public function testRequestTargetDoesNotAllowSpaces()
    {
        $request1 = new Request('/', 'GET');
        $request1->withRequestTarget('/foo bar');
    }

    public function testRequestTargetDefaultsToSlash()
    {
        $request1 = new Request('', 'GET');

        self::assertEquals('/', $request1->getRequestTarget());

        $request2 = new Request('*', 'GET');

        self::assertEquals('*', $request2->getRequestTarget());

        $request3 = new Request('http://foo.com/bar baz/', 'GET');

        self::assertEquals('/bar%20baz/', $request3->getRequestTarget());
    }

    public function testBuildsRequestTarget()
    {
        $request1 = new Request('http://foo.com/baz?bar=bam', 'GET');

        self::assertEquals('/baz?bar=bam', $request1->getRequestTarget());
    }

    public function testBuildsRequestTargetWithFalseyQuery()
    {
        $request1 = new Request('http://foo.com/baz?0', 'GET');

        self::assertEquals('/baz?0', $request1->getRequestTarget());
    }

    public function testHostIsAddedFirst()
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', ['Foo' => 'Bar']);

        self::assertEquals([
            'Host' => ['foo.com'],
            'Foo'  => ['Bar'],
        ], $request->getHeaders());
    }

    public function testCanGetHeaderAsCsv()
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', [
            'Foo' => ['a', 'b', 'c'],
        ]);

        self::assertEquals('a,b,c', $request->getHeaderLine('Foo'));
        self::assertEquals('', $request->getHeaderLine('Bar'));
    }

    public function testHostIsNotOverwrittenWhenPreservingHost()
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', ['Host' => 'a.com']);

        self::assertEquals(['Host' => ['a.com']], $request->getHeaders());

        $request2 = $request->withUri(new Uri('http://www.foo.com/bar'), true);

        self::assertEquals('a.com', $request2->getHeaderLine('Host'));
    }

    public function testOverridesHostWithUri()
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET');

        self::assertEquals(['Host' => ['foo.com']], $request->getHeaders());

        $request2 = $request->withUri(new Uri('http://www.baz.com/bar'));

        self::assertEquals('www.baz.com', $request2->getHeaderLine('Host'));
    }

    public function testAggregatesHeaders()
    {
        $request = new Request('', 'GET', [
            'ZOO' => 'zoobar',
            'zoo' => ['foobar', 'zoobar'],
        ]);

        self::assertEquals(['ZOO' => ['zoobar', 'foobar', 'zoobar']], $request->getHeaders());
        self::assertEquals('zoobar,foobar,zoobar', $request->getHeaderLine('zoo'));
    }

    public function testAddsPortToHeader()
    {
        $request = new Request('http://foo.com:8124/bar', 'GET');

        self::assertEquals('foo.com:8124', $request->getHeaderLine('host'));
    }

    public function testAddsPortToHeaderAndReplacePreviousPort()
    {
        $request = new Request('http://foo.com:8124/bar', 'GET');
        $request = $request->withUri(new Uri('http://foo.com:8125/bar'));

        self::assertEquals('foo.com:8125', $request->getHeaderLine('host'));
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
