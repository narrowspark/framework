<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Response\EmptyResponse;

class EmptyResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $response = new EmptyResponse([], 201);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('', (string) $response->getBody());
        self::assertEquals(201, $response->getStatusCode());
    }

    public function testHeaderConstructor(): void
    {
        $response = new EmptyResponse(['x-empty' => ['true']]);

        self::assertInstanceOf(Response::class, $response);
        self::assertEquals('', (string) $response->getBody());
        self::assertEquals(204, $response->getStatusCode());
        self::assertEquals('true', $response->getHeaderLine('x-empty'));
    }
}
