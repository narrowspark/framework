<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\HttpFactory\Tests;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\HttpFactory\RequestFactory;
use Viserio\Component\HttpFactory\UriFactory;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class RequestFactoryTest extends TestCase
{
    /** @var RequestFactoryInterface */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = new RequestFactory();
    }

    public static function provideCreateRequestCases(): iterable
    {
        return [
            ['GET'],
            ['POST'],
            ['PUT'],
            ['DELETE'],
            ['OPTIONS'],
            ['HEAD'],
        ];
    }

    /**
     * @dataProvider provideCreateRequestCases
     */
    public function testCreateRequest($method): void
    {
        $uri = 'http://example.com/';

        $request = $this->factory->createRequest($method, $uri);

        $this->assertRequest($request, $method, $uri);
    }

    public function testCreateRequestWithUri(): void
    {
        $method = 'GET';
        $uri = 'http://example.com/';

        $request = $this->factory->createRequest($method, $this->createUri($uri));

        $this->assertRequest($request, $method, $uri);
    }

    /**
     * {@inheritdoc}
     */
    protected function createUri($uri): UriInterface
    {
        $uriFactory = new UriFactory();

        return $uriFactory->createUri($uri);
    }

    protected function assertRequest($request, $method, $uri): void
    {
        self::assertInstanceOf(RequestInterface::class, $request);
        self::assertSame($method, $request->getMethod());
        self::assertSame($uri, (string) $request->getUri());
    }
}
