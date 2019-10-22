<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Http\Tests\Response;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\Response\RedirectResponse;
use Viserio\Component\Http\Uri;

/**
 * @internal
 *
 * @small
 */
final class RedirectResponseTest extends TestCase
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
        $uri = Uri::createFromString('https://example.com:10082/foo/bar');
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
}
