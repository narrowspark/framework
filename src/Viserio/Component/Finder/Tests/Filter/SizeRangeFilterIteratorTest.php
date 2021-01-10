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

namespace Viserio\Component\Finder\Tests\Filter;

use Viserio\Component\Finder\Comparator\NumberComparator;
use Viserio\Component\Finder\Filter\SizeRangeFilterIterator;
use Viserio\Component\Finder\Tests\AbstractRealIteratorTestCase;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class SizeRangeFilterIteratorTest extends AbstractRealIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
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

    protected static function getTempPath(): string
    {
        return dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
