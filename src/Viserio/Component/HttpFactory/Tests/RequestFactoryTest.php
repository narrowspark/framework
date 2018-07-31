<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\UriFactory;

/**
 * @internal
 */
final class RequestFactoryTest extends TestCase
{
    /**
     * @var \Psr\Http\Message\RequestFactoryInterface
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
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
        static::assertInstanceOf(RequestInterface::class, $request);
        static::assertSame($method, $request->getMethod());
        static::assertSame($uri, (string) $request->getUri());
    }
}
