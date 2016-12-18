<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests;

use Psr\Http\Message\RequestInterface;
use Viserio\HttpFactory\RequestFactory;
use Viserio\HttpFactory\UriFactory;

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
     *
     * @param mixed $method
     */
    public function testCreateRequest($method)
    {
        $uri     = 'http://example.com/';
        $request = $this->factory->createRequest($method, $uri);

        self::assertRequest($request, $method, $uri);
    }

    public function testCreateRequestWithUri()
    {
        $uriFactory = new UriFactory();
        $method     = 'GET';
        $uri        = 'http://example.com/';
        $request    = $this->factory->createRequest($method, $uriFactory->createUri($uri));

        self::assertRequest($request, $method, $uri);
    }

    private function assertRequest($request, $method, $uri)
    {
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame($method, $request->getMethod());
        self::assertSame($uri, (string) $request->getUri());
    }
}
