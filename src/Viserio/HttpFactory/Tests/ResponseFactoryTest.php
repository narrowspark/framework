<?php
declare(strict_types=1);
namespace Viserio\HttpFactory\Tests;

use Psr\Http\Message\ResponseInterface;
use Viserio\HttpFactory\ResponseFactory;

class ResponseFactoryTest extends \PHPUnit_Framework_TestCase
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
     */
    public function testCreateResponse($code)
    {
        $response = $this->factory->createResponse($code);

        $this->assertResponse($response, $code);
    }

    private function assertResponse($response, $code)
    {
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertSame($code, $response->getStatusCode());
    }
}
