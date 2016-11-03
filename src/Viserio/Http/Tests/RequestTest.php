<?php
declare(strict_types=1);
namespace Viserio\Http\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;
use StdClass;
use Viserio\Http\Request;
use Viserio\Http\Stream;
use Viserio\Http\Stream\FnStream;
use Viserio\Http\Uri;

class RequestTest extends AbstractMessageTest
{
    use MockeryTrait;

    private $mockUri;

    public function setUp()
    {
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
        $this->assertInstanceOf(RequestInterface::class, $this->classToTest);
    }

    public function testValidDefaultRequestTarget()
    {
        $message = $this->classToTest;
        $target = $message->getRequestTarget();

        $this->assertInternalType('string', $target, 'getRequestTarget must return a string');
        $this->assertEquals(
            '/',
            $target,
            'If no URI is available, and no request-target has been specifically provided, this method MUST return the string "/"'
        );
    }

    public function testValidDefaultMethod()
    {
        $message = $this->classToTest;
        $target = $message->getMethod();

        $this->assertInternalType('string', $target, 'getMethod must return a string');
    }

    public function testValidDefaultUri()
    {
        $message = $this->classToTest;
        $body = $message->getUri();

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
    public function testValidWithRequestTarget($expectedRequestTarget)
    {
        $request = $this->classToTest;
        $requestClone = clone $request;
        $newRequest = $request->withRequestTarget($expectedRequestTarget);

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
    public function testValidWithMethod($expectedMethod)
    {
        $request = $this->classToTest;
        $requestClone = clone $request;
        $newRequest = $request->withMethod($expectedMethod);

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
            'GET' => ['GET'],
            'POST' => ['POST'],
            'PUT' => ['PUT'],
            'DELETE' => ['DELETE'],
            'PATCH' => ['PATCH'],
            'OPTIONS' => ['OPTIONS'],
        ];
    }

    public function testValidWithUri()
    {
        $request = $this->classToTest;
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

        $this->assertFalse($streamIsRead);
        $this->assertSame($body, $request->getBody());
    }

    public function testEmptyRequestHostEmptyUriHostPreserveHostFalse()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $requestAfterUri = $this->getEmptyHostHeader()->withUri($uri, false);

        $this->assertEquals('', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostEmptyUriHostPreserveHostTrue()
    {
        $requestAfterUri = $this->getEmptyHostHeader()->withUri($this->mock(UriInterface::class), true);

        $this->assertEquals('', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostDefaultUriHostPreserveHostFalse()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = (new Request($uri))->withUri($this->getDefaultUriHost(), false);

        $this->assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testEmptyRequestHostDefaultUriHostPreserveHostTrue()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');

        $requestAfterUri = (new Request($uri))->withUri($this->getDefaultUriHost(), false);

        $this->assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostEmptyUriHostPreserveHostFalse()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($uri, false);

        $this->assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostEmptyUriHostPreserveHostTrue()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($uri, true);

        $this->assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostDefaultUriHostPreserveHostFalse()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), false);

        $this->assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testDefaultRequestHostDefaultUriHostPreserveHostTrue()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), true);

        $this->assertEquals('foo.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testURIPortIsIgnoredIfHostIsEmpty()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once();
        $request = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHost(), false);

        $this->assertEquals('baz.com', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testURIPortIsUsedForBuildHostHeader()
    {
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getHost')
            ->once()
            ->andReturn('');
        $request = (new Request($uri))->withHeader('Host', 'foo.com');
        $requestAfterUri = $request->withUri($this->getDefaultUriHostAndPort(), false);

        $this->assertEquals('baz.com:8080', $requestAfterUri->getHeaderLine('Host'));
    }

    public function testHostHeaderSetFromUriOnCreationIfNoHostHeaderSpecified()
    {
        $request = new Request('http://www.example.com');

        $this->assertTrue($request->hasHeader('Host'));
        $this->assertEquals('www.example.com', $request->getHeaderLine('host'));
    }

    public function testHostHeaderNotSetFromUriOnCreationIfHostHeaderSpecified()
    {
        $request = new Request('http://www.example.com', null, ['Host' => 'www.test.com'], 'php://memory');

        $this->assertEquals('www.test.com', $request->getHeaderLine('host'));
    }

    public function testRequestUriMayBeString()
    {
        $request = new Request('/', 'GET');

        $this->assertEquals('/', (string) $request->getUri());
    }

    public function testRequestUriMayBeUri()
    {
        $uri = new Uri('/');
        $request = new Request($uri, 'GET');

        $this->assertSame($uri, $request->getUri());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Unable to parse URI: ///.
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

        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertEquals('baz', (string) $request->getBody());
    }

    public function testNullBody()
    {
        $request = new Request('/', 'GET', [], null);

        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('', (string) $request->getBody());
    }

    public function testFalseyBody()
    {
        $request = new Request('/', 'GET', [], '0');

        $this->assertInstanceOf(StreamInterface::class, $request->getBody());
        $this->assertSame('0', (string) $request->getBody());
    }

    public function testCapitalizesMethod()
    {
        $request = new Request('/', 'get');

        $this->assertEquals('GET', $request->getMethod());
    }

    public function testCapitalizesWithMethod()
    {
        $request = new Request('/', 'GET');

        $this->assertEquals('PUT', $request->withMethod('put')->getMethod());
    }

    public function testWithUri()
    {
        $request1 = new Request('/', 'GET');
        $uri1 = $request1->getUri();

        $uri2 = new Uri('http://www.example.com');
        $request2 = $request1->withUri($uri2);

        $this->assertNotSame($request1, $request2);
        $this->assertSame($uri2, $request2->getUri());
        $this->assertSame($uri1, $request1->getUri());
    }

    public function testSameInstanceWhenSameUri()
    {
        $request1 = new Request('http://foo.com', 'GET');
        $request2 = $request1->withUri($request1->getUri());

        $this->assertSame($request1, $request2);
    }

    public function testWithRequestTarget()
    {
        $request1 = new Request('/', 'GET');
        $request2 = $request1->withRequestTarget('*');

        $this->assertEquals('*', $request2->getRequestTarget());
        $this->assertEquals('/', $request1->getRequestTarget());
    }

    public function testWithRequestNullUri()
    {
        $request = new Request(null, 'GET');

        $this->assertEquals('/', $request->getRequestTarget());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid URI provided; must be null, a string, or a Psr\Http\Message\UriInterface instance.
     */
    public function testRequestToThrowException()
    {
        new Request(new StdClass(), 'GET');
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

        $this->assertEquals('/', $request1->getRequestTarget());

        $request2 = new Request('*', 'GET');

        $this->assertEquals('*', $request2->getRequestTarget());

        $request3 = new Request('http://foo.com/bar baz/', 'GET');

        $this->assertEquals('/bar%20baz/', $request3->getRequestTarget());
    }

    public function testBuildsRequestTarget()
    {
        $request1 = new Request('http://foo.com/baz?bar=bam', 'GET');

        $this->assertEquals('/baz?bar=bam', $request1->getRequestTarget());
    }

    public function testBuildsRequestTargetWithFalseyQuery()
    {
        $request1 = new Request('http://foo.com/baz?0', 'GET');

        $this->assertEquals('/baz?0', $request1->getRequestTarget());
    }

    public function testHostIsAddedFirst()
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', ['Foo' => 'Bar']);

        $this->assertEquals([
            'Host' => ['foo.com'],
            'Foo' => ['Bar'],
        ], $request->getHeaders());
    }

    public function testCanGetHeaderAsCsv()
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', [
            'Foo' => ['a', 'b', 'c'],
        ]);

        $this->assertEquals('a,b,c', $request->getHeaderLine('Foo'));
        $this->assertEquals('', $request->getHeaderLine('Bar'));
    }

    public function testHostIsNotOverwrittenWhenPreservingHost()
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET', ['Host' => 'a.com']);

        $this->assertEquals(['Host' => ['a.com']], $request->getHeaders());

        $request2 = $request->withUri(new Uri('http://www.foo.com/bar'), true);

        $this->assertEquals('a.com', $request2->getHeaderLine('Host'));
    }

    public function testOverridesHostWithUri()
    {
        $request = new Request('http://foo.com/baz?bar=bam', 'GET');

        $this->assertEquals(['Host' => ['foo.com']], $request->getHeaders());

        $request2 = $request->withUri(new Uri('http://www.baz.com/bar'));

        $this->assertEquals('www.baz.com', $request2->getHeaderLine('Host'));
    }

    public function testAggregatesHeaders()
    {
        $request = new Request('', 'GET', [
            'ZOO' => 'zoobar',
            'zoo' => ['foobar', 'zoobar'],
        ]);

        $this->assertEquals(['ZOO' => ['zoobar', 'foobar', 'zoobar']], $request->getHeaders());
        $this->assertEquals('zoobar,foobar,zoobar', $request->getHeaderLine('zoo'));
    }

    public function testAddsPortToHeader()
    {
        $request = new Request('http://foo.com:8124/bar', 'GET');

        $this->assertEquals('foo.com:8124', $request->getHeaderLine('host'));
    }

    public function testAddsPortToHeaderAndReplacePreviousPort()
    {
        $request = new Request('http://foo.com:8124/bar', 'GET');
        $request = $request->withUri(new Uri('http://foo.com:8125/bar'));

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
