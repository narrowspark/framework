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

use Viserio\Component\Finder\Comparator\DateComparator;
use Viserio\Component\Finder\Filter\DateRangeFilterIterator;
use Viserio\Component\Finder\Tests\Fixture\Iterator;
use Viserio\Component\Finder\Tests\RealIteratorTestCase;

/**
 * @internal
 *
 * @small
 */
final class DateRangeFilterIteratorTest extends RealIteratorTestCase
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$files[] = self::toAbsolute('atime.php');
    }

    /**
     * @dataProvider provideAcceptCases
     *
     * @param mixed $size
     * @param mixed $expected
     */
    public function testAccept($size, $expected): void
    {
        $files = (array) self::$files;
        $files[] = self::toAbsolute('doesnotexist');

        $iterator = new DateRangeFilterIterator(new Iterator($files), $size);

        $this->assertIterator($expected, $iterator);
    }

    public function provideAcceptCases(): iterable
    {
        $since20YearsAgo = [
            '.git',
            'test.py',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'toto',
            'toto/.git',
            'atime.php',
            '.bar',
            '.foo',
            '.foo/.bar',
            'foo bar',
            '.foo/bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ];

        $since2MonthsAgo = [
            '.git',
            'test.py',
            'foo',
            'toto',
            'toto/.git',
            '.bar',
            '.foo',
            '.foo/.bar',
            'foo bar',
            '.foo/bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ];

        $accessedSince2MonthsAgo = [
            '.git',
            'test.py',
            'foo',
            'toto',
            'toto/.git',
            '.bar',
            '.foo',
            '.foo/.bar',
            'foo bar',
            '.foo/bar',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'atime.php',
        ];

        $untilLastMonth = [
            'atime.php',
            'foo/bar.tmp',
            'test.php',
        ];

        $accessedUntilLastMonth = [
            'foo/bar.tmp',
            'test.php',
        ];

        yield [[new DateComparator('since 20 years ago', DateComparator::LAST_ACCESSED)], self::toAbsolute($since20YearsAgo)];

        yield [[new DateComparator('since 20 years ago', DateComparator::LAST_CHANGED)], self::toAbsolute($since20YearsAgo)];

        yield [[new DateComparator('since 20 years ago', DateComparator::LAST_MODIFIED)], self::toAbsolute($since20YearsAgo)];

        yield [[new DateComparator('since 2 months ago', DateComparator::LAST_ACCESSED)], self::toAbsolute($accessedSince2MonthsAgo)];

        yield [[new DateComparator('since 2 months ago', DateComparator::LAST_CHANGED)], self::toAbsolute($since20YearsAgo)];

        yield [[new DateComparator('since 2 months ago', DateComparator::LAST_MODIFIED)], self::toAbsolute($since2MonthsAgo)];

        yield [[new DateComparator('until last month', DateComparator::LAST_ACCESSED)], self::toAbsolute($accessedUntilLastMonth)];

        yield [[new DateComparator('until last month', DateComparator::LAST_CHANGED)], self::toAbsolute([])];

        yield [[new DateComparator('until last month', DateComparator::LAST_MODIFIED)], self::toAbsolute($untilLastMonth)];
    }

    /**
     * @return string
     */
    protected static function getTempPath(): string
    {
        return dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
