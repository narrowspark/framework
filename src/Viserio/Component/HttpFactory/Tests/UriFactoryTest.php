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

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\HttpFactory\UriFactory;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class UriFactoryTest extends TestCase
{
    /** @var UriFactoryInterface */
    protected $factory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->factory = new UriFactory();
    }

    public function testCreateUri(): void
    {
        $uriString = 'http://example.com/';

        $uri = $this->factory->createUri($uriString);

        $this->assertUri($uri, $uriString);
    }

    public function testExceptionWhenUriIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->factory->createUri(':');
    }

    protected function assertUri($uri, $uriString): void
    {
        self::assertInstanceOf(UriInterface::class, $uri);
        self::assertSame($uriString, (string) $uri);
    }
}
