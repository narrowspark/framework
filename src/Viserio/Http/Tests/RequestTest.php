<?php
namespace Viserio\Http\Tests;

use Psr\Http\Message\RequestInterface;
use Viserio\Http\Request;

class RequestTest extends AbstractMessageTest
{
    public function setUp()
    {
        $this->classToTest = new Request('GET', $this->getMock('Psr\Http\Message\UriInterface'));
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
            'Psr\Http\Message\UriInterface',
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
        $uri = $this->getMock('Psr\Http\Message\UriInterface');
        $newRequest = $request->withUri($uri);
        $this->assertImmutable($requestClone, $request, $newRequest);
        $this->assertEquals(
            $uri,
            $newRequest->getUri(),
            'getUri does not match request target set in withUri'
        );
    }

    /**
     * @dataProvider hostHeaderPreservationWhenUriIsSetProvider
     *
     * @param RequestInterface $request
     * @param UriInterface     $uri
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
        $emptyUriHost = $this->getMock('Psr\Http\Message\UriInterface');
        $defaultUriHost = $this->getMock('Psr\Http\Message\UriInterface');
        $defaultUriHost->expects(TestCase::any())
            ->method('getHost')
            ->willReturn('baz.com')
        ;
        $defaultUriPort = $this->getMock('Psr\Http\Message\UriInterface');
        $defaultUriPort->expects(TestCase::any())
            ->method('getPort')
            ->willReturn('8080')
        ;
        $defaultUriHostAndPort = $this->getMock('Psr\Http\Message\UriInterface');
        $defaultUriHostAndPort->expects(TestCase::any())
            ->method('getHost')
            ->willReturn('baz.com')
        ;
        $defaultUriHostAndPort->expects(TestCase::any())
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
}
