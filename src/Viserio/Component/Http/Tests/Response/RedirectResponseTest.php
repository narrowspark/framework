<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\RedirectResponse;
use Viserio\Component\Http\Uri;

class RedirectResponseTest extends TestCase
{
    public function testConstructorAcceptsStringUriAndProduces302ResponseWithLocationHeader(): void
    {
        $response = new RedirectResponse('/foo/bar');

        self::assertEquals(302, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertEquals('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAcceptsUriInstanceAndProduces302ResponseWithLocationHeader(): void
    {
        $uri      = Uri::createFromString('https://example.com:10082/foo/bar');
        $response = new RedirectResponse($uri);

        self::assertEquals(302, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertEquals((string) $uri, $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingAlternateStatusCode(): void
    {
        $response = new RedirectResponse('/foo/bar', 301);

        self::assertEquals(301, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertEquals('/foo/bar', $response->getHeaderLine('Location'));
    }

    public function testConstructorAllowsSpecifyingHeaders(): void
    {
        $response = new RedirectResponse('/foo/bar', 302, ['X-Foo' => ['Bar']]);

        self::assertEquals(302, $response->getStatusCode());
        self::assertTrue($response->hasHeader('Location'));
        self::assertEquals('/foo/bar', $response->getHeaderLine('Location'));
        self::assertTrue($response->hasHeader('X-Foo'));
        self::assertEquals('Bar', $response->getHeaderLine('X-Foo'));
    }

    public function invalidUris()
    {
        return [
            'null'       => [null],
            'false'      => [false],
            'true'       => [true],
            'zero'       => [0],
            'int'        => [1],
            'zero-float' => [0.0],
            'float'      => [1.1],
            'array'      => [['/foo/bar']],
            'object'     => [(object) ['/foo/bar']],
        ];
    }

    /**
     * @dataProvider invalidUris
     * @expectedException \Viserio\Component\Contracts\Http\Exception\UnexpectedValueException Uri
     *
     * @param mixed $uri
     */
    public function testConstructorRaisesExceptionOnInvalidUri($uri): void
    {
        new RedirectResponse($uri);
    }
}
