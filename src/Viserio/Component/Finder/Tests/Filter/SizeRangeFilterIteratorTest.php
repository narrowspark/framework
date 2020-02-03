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

namespace Viserio\Component\Finder\Tests\Filter;

use Viserio\Component\Finder\Comparator\NumberComparator;
use Viserio\Component\Finder\Filter\SizeRangeFilterIterator;
use Viserio\Component\Finder\Tests\AbstractRealIteratorTestCase;

/**
 * @internal
 *
 * @small
 */
final class SizeRangeFilterIteratorTest extends AbstractRealIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     *
     * @param mixed $size
     * @param mixed $expected
     */
    public function testAccept($size, $expected): void
    {
        $inner = new \Viserio\Component\Finder\Tests\Fixture\InnerSizeIterator(self::$files);

        $iterator = new SizeRangeFilterIterator($inner, $size);

        $this->assertIterator($expected, $iterator);
    }

    /**
     * @return iterable<array<int, array<string|\Viserio\Component\Finder\Comparator\NumberComparator>|string>>
     */
    public static function provideAcceptCases(): iterable
    {
        $lessThan1KGreaterThan05K = [
            '.foo',
            '.git',
            'foo',
            'qux',
            'test.php',
            'toto',
            'toto/.git',
        ];

        yield [[new NumberComparator('< 1K'), new NumberComparator('> 0.5K')], self::toAbsolute($lessThan1KGreaterThan05K)];
    }

    /**
     * @return string
     */
    protected static function getTempPath(): string
    {
        return dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
