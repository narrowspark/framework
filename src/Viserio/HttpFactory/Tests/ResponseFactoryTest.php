<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests;

use Psr\Http\Message\ResponseInterface;
use Viserio\HttpFactory\ResponseFactory;
use PHPUnit\Framework\TestCase;

class ResponseFactoryTest extends TestCase
{
    private $factory;

    public function setUp()
    {
        $this->factory = new ResponseFactory();
    }

    public function dataCodes()
    {
        return [
            [200],
            [301],
            [404],
            [500],
        ];
    }

    /**
     * @dataProvider dataCodes
     *
     * @param mixed $code
     */
    public function testCreateResponse($code)
    {
        $response = $this->factory->createResponse($code);

        self::assertResponse($response, $code);
    }

    private function assertResponse($response, $code)
    {
        self::assertInstanceOf(ResponseInterface::class, $response);

        self::assertSame($code, $response->getStatusCode());
    }
}
