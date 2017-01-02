<?php
declare(strict_types=1);
namespace Viserio\Http\Tests\Response;

use Viserio\Http\Response;
use Viserio\Http\Response\EmptyResponse;
use PHPUnit\Framework\TestCase;

class EmptyResponseTest extends TestCase
{
    public function testConstructor()
    {
        $response = new EmptyResponse([], 201);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('', (string) $response->getBody());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testHeaderConstructor()
    {
        $response = new EmptyResponse(['x-empty' => ['true']]);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('', (string) $response->getBody());
        self::assertEquals(204, $response->getStatusCode());
        self::assertEquals('true', $response->getHeaderLine('x-empty'));
    }
}
