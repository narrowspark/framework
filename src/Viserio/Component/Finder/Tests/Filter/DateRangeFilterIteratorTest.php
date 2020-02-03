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
use Viserio\Component\Finder\Tests\AbstractRealIteratorTestCase;
use Viserio\Component\Finder\Tests\Fixture\Iterator;

/**
 * @internal
 *
 * @small
 */
final class DateRangeFilterIteratorTest extends AbstractRealIteratorTestCase
{
    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        /** @var string $atimePath */
        $atimePath = self::toAbsolute('atime.php');

        self::$files[] = $atimePath;
    }

    /**
     * @dataProvider provideAcceptCases
     *
     * @param \Viserio\Component\Finder\Comparator\DateComparator[] $size
     * @param string[]                                              $expected
     */
    public function testAccept(array $size, array $expected): void
    {
        $files = self::$files;
        $files[] = self::toAbsolute('doesnotexist');

        $iterator = new DateRangeFilterIterator(new Iterator($files), $size);

        $this->assertIterator($expected, $iterator);
    }

    /**
     * @return iterable<array<int, array<string|\Viserio\Component\Finder\Comparator\DateComparator>|string>>
     */
    public static function provideAcceptCases(): iterable
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
