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

namespace Viserio\Component\Filesystem\Tests\Iterator;

use ArrayIterator;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Iterator\RegexFilterIterator;

/**
 * @internal
 *
 * @small
 */
final class RegexFilterIteratorTest extends TestCase
{
    public function testIterate(): void
    {
        $values = [
            '/foo',
            '/foo/bar',
            '/foo/bar/baz',
            '/foo/baz',
            '/bar',
        ];
        $expected = [
            '/foo',
            '/foo/bar',
            '/foo/baz',
        ];
        $iterator = new RegexFilterIterator(
            '~^/foo(/[^/]+)?$~',
            '/foo',
            new ArrayIterator($values)
        );
        self::assertSame($expected, \iterator_to_array($iterator));
        self::assertFalse($iterator->valid());
        self::assertNull($iterator->key());
        self::assertNull($iterator->current());
    }

    public function testIterateTwice(): void
    {
        $values = [
            '/foo',
            '/foo/bar',
            '/foo/bar/baz',
            '/foo/baz',
            '/bar',
        ];
        $expected = [
            '/foo',
            '/foo/bar',
            '/foo/baz',
        ];
        $iterator = new RegexFilterIterator(
            '~^/foo(/[^/]+)?$~',
            '/foo',
            new ArrayIterator($values)
        );
        // Make sure everything is rewound correctly
        self::assertSame($expected, \iterator_to_array($iterator));
        self::assertSame($expected, \iterator_to_array($iterator));
        self::assertFalse($iterator->valid());
        self::assertNull($iterator->key());
        self::assertNull($iterator->current());
    }

    public function testIterateKeyAsKey(): void
    {
        $values = [
            'a' => '/foo',
            'b' => '/foo/bar',
            'c' => '/foo/bar/baz',
            'd' => '/foo/baz',
            'e' => '/bar',
        ];
        $expected = [
            'a' => '/foo',
            'b' => '/foo/bar',
            'd' => '/foo/baz',
        ];
        $iterator = new RegexFilterIterator(
            '~^/foo(/[^/]+)?$~',
            '/foo',
            new ArrayIterator($values),
            RegexFilterIterator::KEY_AS_KEY
        );
        self::assertSame($expected, \iterator_to_array($iterator));
        self::assertFalse($iterator->valid());
        self::assertNull($iterator->key());
        self::assertNull($iterator->current());
    }
}
