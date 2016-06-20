<?php
namespace Viserio\Http\Tests\Response;

use Viserio\Http\Response\RedirectResponse;
use Viserio\Http\Uri;

class RedirectResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorAcceptsStringUriAndProduces302ResponseWithLocationHeader()
    {
        $response = new RedirectResponse('/foo/bar');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertEquals('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAcceptsUriInstanceAndProduces302ResponseWithLocationHeader()
    {
        $uri = new Uri('https://example.com:10082/foo/bar');
        $response = new RedirectResponse($uri);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertEquals((string) $uri, $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingAlternateStatusCode()
    {
        $response = new RedirectResponse('/foo/bar', 301);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertEquals('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingHeaders()
    {
        $response = new RedirectResponse('/foo/bar', 302, ['X-Foo' => ['Bar']]);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Location'));
        $this->assertEquals('/foo/bar', $response->getHeaderLine('Location'));
        $this->assertTrue($response->hasHeader('X-Foo'));
        $this->assertEquals('Bar', $response->getHeaderLine('X-Foo'));
    }

    public function invalidUris()
    {
        return [
            'null'       => [ null ],
            'false'      => [ false ],
            'true'       => [ true ],
            'zero'       => [ 0 ],
            'int'        => [ 1 ],
            'zero-float' => [ 0.0 ],
            'float'      => [ 1.1 ],
            'array'      => [ [ '/foo/bar' ] ],
            'object'     => [ (object) [ '/foo/bar' ] ],
        ];
    }

    /**
     * @dataProvider invalidUris
     * @expectedException InvalidArgumentException Uri
     */
    public function testConstructorRaisesExceptionOnInvalidUri($uri)
    {
        $response = new RedirectResponse($uri);
    }
}
