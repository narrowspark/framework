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

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('', (string) $response->getBody());
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testConstructorWithHeader(): void
    {
        $response = new EmptyResponse(['x-empty' => ['true']]);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals('', (string) $response->getBody());
        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals('true', $response->getHeaderLine('x-empty'));
    }
}
