<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\UriFactory;

class RequestFactoryTest extends TestCase
{
    /**
     * @var \Interop\Http\Factory\RequestFactoryInterface
     */
    private $factory;

    public function setUp(): void
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
    public function testCreateRequest($method): void
    {
        $uri     = 'http://example.com/';
        $request = $this->factory->createRequest($method, $uri);

        $this->assertRequest($request, $method, $uri);
    }

    public function testCreateRequestWithUri(): void
    {
        $uriFactory = new UriFactory();
        $method     = 'GET';
        $uri        = 'http://example.com/';
        $request    = $this->factory->createRequest($method, $uriFactory->createUri($uri));

        $this->assertRequest($request, $method, $uri);
    }

    private function assertRequest($request, $method, $uri): void
    {
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame($method, $request->getMethod());
        self::assertSame($uri, (string) $request->getUri());
    }
}
