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
