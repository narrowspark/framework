<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\HttpFactory\ServerRequestFactory;

/**
 * @internal
 */
final class ServerRequestFactoryTest extends TestCase
{
    /**
     * @var \Viserio\Component\HttpFactory\ServerRequestFactory
     */
    private $serverRequestFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->serverRequestFactory = new ServerRequestFactory();
    }

    public function testCreateServerRequestWithEmptyMethod(): void
    {
        $serverRequest = $this->serverRequestFactory->createServerRequest('', '/', ['REQUEST_METHOD' => 'GET']);

        $this->assertSame('GET', $serverRequest->getMethod());
    }

    public function testCreateServerRequestWithEmptyMethodAndRequestMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine HTTP method.');

        $this->serverRequestFactory->createServerRequest('', '/');
    }
}
