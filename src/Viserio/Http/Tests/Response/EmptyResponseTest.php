<?php
namespace Viserio\Http\Tests\Response;

use Viserio\Http\Response;
use Viserio\Http\Response\EmptyResponse;

class EmptyResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $response = new EmptyResponse([], 201);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('', (string) $response->getBody());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testHeaderConstructor()
    {
        $response = new EmptyResponse(['x-empty' => ['true']]);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('', (string) $response->getBody());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('true', $response->getHeaderLine('x-empty'));
    }
}
