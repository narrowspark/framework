<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Viserio\Component\HttpFactory\ResponseFactory;

class ResponseFactoryTest extends TestCase
{
    /**
     * @var \Interop\Http\Factory\ResponseFactoryInterface
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
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
    public function testCreateResponse($code): void
    {
        $response = $this->factory->createResponse($code);

        $this->assertResponse($response, $code);
    }

    private function assertResponse($response, $code): void
    {
        self::assertInstanceOf(ResponseInterface::class, $response);

        self::assertSame($code, $response->getStatusCode());
    }
}
