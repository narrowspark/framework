<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests;

use Viserio\HttpFactory\RequestFactory;
use Viserio\HttpFactory\UriFactory;
use Psr\Http\Message\RequestInterface;

class RequestFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new RequestFactory();
    }

    public function dataMethods()
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
            ['OPTIONS'],
            ['HEAD'],
        ];
    }

    /**
     * @dataProvider dataMethods
     */
    public function testCreateRequest($method)
    {
        $uri = 'http://example.com/';
        $request = $this->factory->createRequest($method, $uri);

        $this->assertRequest($request, $method, $uri);
    }

    public function testCreateRequestWithUri()
    {
        $uriFactory = new UriFactory();
        $method = 'GET';
        $uri = 'http://example.com/';
        $request = $this->factory->createRequest($method, $uriFactory->createUri($uri));

        $this->assertRequest($request, $method, $uri);
    }

    private function assertRequest($request, $method, $uri)
    {
        $this->assertInstanceOf(RequestInterface::class, $request);
        $this->assertSame($method, $request->getMethod());
        $this->assertSame($uri, (string) $request->getUri());
    }
}
