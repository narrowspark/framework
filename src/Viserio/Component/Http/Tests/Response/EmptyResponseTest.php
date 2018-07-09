<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response;
use Viserio\Component\Http\Response\EmptyResponse;

/**
 * @internal
 */
final class EmptyResponseTest extends TestCase
{
    public function testConstructor(): void
    {
        $response = new EmptyResponse([], 201);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals('', (string) $response->getBody());
        static::assertEquals(201, $response->getStatusCode());
    }

    public function testConstructorWithHeader(): void
    {
        $response = new EmptyResponse(['x-empty' => ['true']]);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals('', (string) $response->getBody());
        static::assertEquals(204, $response->getStatusCode());
        static::assertEquals('true', $response->getHeaderLine('x-empty'));
    }
}
