<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\HttpFactory\UriFactory;

/**
 * @internal
 */
final class UriFactoryTest extends TestCase
{
    /**
     * @var UriFactoryInterface
     */
    private $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->factory = new UriFactory();
    }

    public function testCreateUri(): void
    {
        $uriString = 'http://example.com/';
        $uri       = $this->factory->createUri($uriString);
        $this->assertUri($uri, $uriString);
    }

    public function testExceptionWhenUriIsInvalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->factory->createUri(':', null);
    }

    /**
     * @param \Psr\Http\Message\UriInterface|string $uri
     * @param string                                $uriString
     */
    protected function assertUri($uri, ?string $uriString): void
    {
        static::assertInstanceOf(UriInterface::class, $uri);
        static::assertSame($uriString, (string) $uri);
    }
}
