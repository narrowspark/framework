<?php
namespace Viserio\Http\Tests;

use Psr\Http\Message\{
    RequestInterface,
    StreamInterface,
    UriInterface
};
use Viserio\Http\{
    Request,
    Util,
    Uri
};
use Viserio\Http\Stream\FnStream;

class RequestTest extends AbstractMessageTest
{
    public function setUp()
    {
        $this->classToTest = new Request($this->getMock(UriInterface::class));
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

    // Test methods for change instances status

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
        /** @var UriInterface $uri */
        $uri = $this->getMock(UriInterface::class);
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

        $body = FnStream::decorate(Util::getStream(''), [
            '__toString' => function () use (&$streamIsRead) {
                $streamIsRead = true;
                return '';
            }
        ]);

        $r = new Request('/', 'GET', [], $body);
        $this->assertFalse($streamIsRead);
        $this->assertSame($body, $r->getBody());
    }

    /**
     * @dataProvider hostHeaderPreservationWhenUriIsSetProvider
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Http\Message\UriInterface     $uri
     * @param bool             $preserveHost
     * @param string[]         $expectedHostHeaderLine
     */
    public function testHostHeaderPreservationWhenUriIsSet(
        RequestInterface $request,
        UriInterface $uri,
        $preserveHost,
        $expectedHostHeaderLine
    ) {
        $requestAfterUri = $request->withUri($uri, $preserveHost);
        $this->assertEquals($expectedHostHeaderLine, $requestAfterUri->getHeaderLine('Host'));
    }

    public function hostHeaderPreservationWhenUriIsSetProvider()
    {
        $emptyHostHeader = $this->classToTest;
        $defaultRequestHostHeader = $this->classToTest->withHeader('Host', 'foo.com');
        $emptyUriHost = $this->getMock(UriInterface::class);
        $defaultUriHost = $this->getMock(UriInterface::class);
        $defaultUriHost->expects(self::any())
            ->method('getHost')
            ->willReturn('baz.com')
        ;
        $defaultUriPort = $this->getMock(UriInterface::class);
        $defaultUriPort->expects(self::any())
            ->method('getPort')
            ->willReturn('8080')
        ;
        $defaultUriHostAndPort = $this->getMock(UriInterface::class);
        $defaultUriHostAndPort->expects(self::any())
            ->method('getHost')
            ->willReturn('baz.com')
        ;
        $defaultUriHostAndPort->expects(self::any())
            ->method('getPort')
            ->willReturn('8080')
        ;

        return [
            // Description => [request, with uri, host header line]
            'empty request host / empty uri host / preserveHost false' => [
                $emptyHostHeader,
                $emptyUriHost,
                false,
                '',
            ],
            'empty request host / empty uri host / preserveHost true' => [
                $emptyHostHeader,
                $emptyUriHost,
                true,
                '',
            ],
            'empty request host / default uri host / preserveHost false' => [
                $emptyHostHeader,
                $defaultUriHost,
                false,
                'baz.com',
            ],
            'empty request host / default uri host / preserveHost true' => [
                $emptyHostHeader,
                $defaultUriHost,
                true,
                'baz.com',
            ],
            'default request host / empty uri host / preserveHost false' => [
                $defaultRequestHostHeader,
                $emptyUriHost,
                false,
                'foo.com',
            ],
            'default request host / empty uri host / preserveHost true' => [
                $defaultRequestHostHeader,
                $emptyUriHost,
                true,
                'foo.com',
            ],
            'default request host / default uri host / preserveHost false' => [
                $defaultRequestHostHeader,
                $defaultUriHost,
                false,
                'baz.com',
            ],
            'default request host / default uri host / preserveHost true' => [
                $defaultRequestHostHeader,
                $defaultUriHost,
                true,
                'foo.com',
            ],
            // URI port test cases
            'URI port is ignored if host is empty' => [
                $defaultRequestHostHeader,
                $defaultUriPort,
                false,
                'foo.com',
            ],
            'URI port is used for build Host Header' => [
                $defaultRequestHostHeader,
                $defaultUriHostAndPort,
                false,
                'baz.com:8080',
            ],
        ];
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
        $r = new Request('/', 'GET');
        $this->assertEquals('/', (string) $r->getUri());
    }

    public function testRequestUriMayBeUri()
    {
        $uri = new Uri('/');
        $r = new Request($uri, 'GET');
        $this->assertSame($uri, $r->getUri());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidateRequestUri()
    {
        new Request('///', 'GET');
    }

    public function testCanConstructWithBody()
    {
        $r = new Request('/', 'GET', [], 'baz');
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertEquals('baz', (string) $r->getBody());
    }

    public function testNullBody()
    {
        $r = new Request('/', 'GET', [], null);
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertSame('', (string) $r->getBody());
    }

    public function testFalseyBody()
    {
        $r = new Request('/', 'GET', [], '0');
        $this->assertInstanceOf(StreamInterface::class, $r->getBody());
        $this->assertSame('0', (string) $r->getBody());
    }

    public function testCapitalizesMethod()
    {
        $r = new Request('/', 'get');
        $this->assertEquals('GET', $r->getMethod());
    }

    public function testCapitalizesWithMethod()
    {
        $r = new Request('/', 'GET');
        $this->assertEquals('PUT', $r->withMethod('put')->getMethod());
    }

    public function testWithUri()
    {
        $r1 = new Request('/', 'GET');
        $u1 = $r1->getUri();

        $u2 = new Uri('http://www.example.com');
        $r2 = $r1->withUri($u2);

        $this->assertNotSame($r1, $r2);
        $this->assertSame($u2, $r2->getUri());
        $this->assertSame($u1, $r1->getUri());
    }

    public function testSameInstanceWhenSameUri()
    {
        $r1 = new Request('http://foo.com', 'GET');
        $r2 = $r1->withUri($r1->getUri());
        $this->assertSame($r1, $r2);
    }

    public function testWithRequestTarget()
    {
        $r1 = new Request('/', 'GET');
        $r2 = $r1->withRequestTarget('*');

        $this->assertEquals('*', $r2->getRequestTarget());
        $this->assertEquals('/', $r1->getRequestTarget());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRequestTargetDoesNotAllowSpaces()
    {
        $r1 = new Request('/', 'GET');
        $r1->withRequestTarget('/foo bar');
    }

    public function testRequestTargetDefaultsToSlash()
    {
        $r1 = new Request('', 'GET');
        $this->assertEquals('/', $r1->getRequestTarget());

        $r2 = new Request('*', 'GET');
        $this->assertEquals('*', $r2->getRequestTarget());

        $r3 = new Request('http://foo.com/bar baz/', 'GET');
        $this->assertEquals('/bar%20baz/', $r3->getRequestTarget());
    }

    public function testBuildsRequestTarget()
    {
        $r1 = new Request('http://foo.com/baz?bar=bam', 'GET');
        $this->assertEquals('/baz?bar=bam', $r1->getRequestTarget());
    }

    public function testBuildsRequestTargetWithFalseyQuery()
    {
        $r1 = new Request('http://foo.com/baz?0', 'GET');
        $this->assertEquals('/baz?0', $r1->getRequestTarget());
    }

    public function testHostIsAddedFirst()
    {
        $r = new Request('http://foo.com/baz?bar=bam', 'GET', ['Foo' => 'Bar']);
        $this->assertEquals([
            'Host' => ['foo.com'],
            'Foo'  => ['Bar'],
        ], $r->getHeaders());
    }

    public function testCanGetHeaderAsCsv()
    {
        $r = new Request('http://foo.com/baz?bar=bam', 'GET', [
            'Foo' => ['a', 'b', 'c'],
        ]);

        $this->assertEquals('a,b,c', $r->getHeaderLine('Foo'));
        $this->assertEquals('', $r->getHeaderLine('Bar'));
    }

    public function testHostIsNotOverwrittenWhenPreservingHost()
    {
        $r = new Request('http://foo.com/baz?bar=bam', 'GET', ['Host' => 'a.com']);

        $this->assertEquals(['Host' => ['a.com']], $r->getHeaders());

        $r2 = $r->withUri(new Uri('http://www.foo.com/bar'), true);

        $this->assertEquals('a.com', $r2->getHeaderLine('Host'));
    }

    public function testOverridesHostWithUri()
    {
        $r = new Request('http://foo.com/baz?bar=bam', 'GET');

        $this->assertEquals(['Host' => ['foo.com']], $r->getHeaders());

        $r2 = $r->withUri(new Uri('http://www.baz.com/bar'));

        $this->assertEquals('www.baz.com', $r2->getHeaderLine('Host'));
    }

    public function testAggregatesHeaders()
    {
        $r = new Request('', 'GET', [
            'ZOO' => 'zoobar',
            'zoo' => ['foobar', 'zoobar'],
        ]);

        $this->assertEquals(['ZOO' => ['zoobar', 'foobar', 'zoobar']], $r->getHeaders());
        $this->assertEquals('zoobar,foobar,zoobar', $r->getHeaderLine('zoo'));
    }

    public function testAddsPortToHeader()
    {
        $r = new Request('http://foo.com:8124/bar', 'GET');

        $this->assertEquals('foo.com:8124', $r->getHeaderLine('host'));
    }

    public function testAddsPortToHeaderAndReplacePreviousPort()
    {
        $r = new Request('http://foo.com:8124/bar', 'GET');
        $r = $r->withUri(new Uri('http://foo.com:8125/bar'));

        $this->assertEquals('foo.com:8125', $r->getHeaderLine('host'));
    }
}
