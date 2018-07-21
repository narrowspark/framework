<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\RedirectResponse;
use Viserio\Component\Http\Uri;

/**
 * @internal
 */
final class RedirectResponseTest extends TestCase
{
    public function testConstructorAcceptsStringUriAndProduces302ResponseWithLocationHeader(): void
    {
        $response = new RedirectResponse('/foo/bar');

        static::assertEquals(302, $response->getStatusCode());
        static::assertTrue($response->hasHeader('Location'));
        static::assertEquals('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAcceptsUriInstanceAndProduces302ResponseWithLocationHeader(): void
    {
        $uri      = Uri::createFromString('https://example.com:10082/foo/bar');
        $response = new RedirectResponse($uri);

        static::assertEquals(302, $response->getStatusCode());
        static::assertTrue($response->hasHeader('Location'));
        static::assertEquals((string) $uri, $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingAlternateStatusCode(): void
    {
        $response = new RedirectResponse('/foo/bar', 301);

        static::assertEquals(301, $response->getStatusCode());
        static::assertTrue($response->hasHeader('Location'));
        static::assertEquals('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingHeaders(): void
    {
        $response = new RedirectResponse('/foo/bar', 302, ['X-Foo' => ['Bar']]);

        static::assertEquals(302, $response->getStatusCode());
        static::assertTrue($response->hasHeader('Location'));
        static::assertEquals('/foo/bar', $response->getHeaderLine('Location'));
        static::assertTrue($response->hasHeader('X-Foo'));
        static::assertEquals('Bar', $response->getHeaderLine('X-Foo'));
    }
}
