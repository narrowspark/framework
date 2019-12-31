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

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Viserio\Component\Finder\Filter\DepthRangeFilterIterator;
use Viserio\Component\Finder\Tests\AbstractRealIteratorTestCase;

/**
 * @internal
 *
 * @small
 */
final class DepthRangeFilterIteratorTest extends AbstractRealIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     *
     * @param int      $minDepth
     * @param int      $maxDepth
     * @param string[] $expected
     */
    public function testAccept(int $minDepth, int $maxDepth, array $expected): void
    {
        /** @var string $path */
        $path = self::toAbsolute();

        $inner = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        $iterator = new DepthRangeFilterIterator($inner, $minDepth, $maxDepth);

        $actual = \array_keys(\iterator_to_array($iterator));

        \sort($expected);
        \sort($actual);

        self::assertEquals($expected, $actual);
    }

    /**
     * @return iterable<array<int, array<string>|int|string>>
     */
    public function provideAcceptCases(): iterable
    {
        $lessThan1 = [
            '.gitignore',
            '.git',
            'test.py',
            'foo',
            'test.php',
            'toto',
            '.foo',
            '.bar',
            'atime.php',
            'foo bar',
            'qux',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ];

        $lessThanOrEqualTo1 = [
            '.gitignore',
            '.git',
            'test.py',
            'foo',
            'foo/bar.tmp',
            'test.php',
            'toto',
            'toto/.git',
            '.foo',
            '.foo/.bar',
            '.bar',
            'atime.php',
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

        $graterThanOrEqualTo1 = [
            'toto/.git',
            'foo/bar.tmp',
            '.foo/.bar',
            '.foo/bar',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
        ];

        $equalTo1 = [
            'toto/.git',
            'foo/bar.tmp',
            '.foo/.bar',
            '.foo/bar',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
        ];

        yield [0, 0, self::toAbsolute($lessThan1)];

        yield [0, 1, self::toAbsolute($lessThanOrEqualTo1)];

        yield [2, \PHP_INT_MAX, []];

        yield [1, \PHP_INT_MAX, self::toAbsolute($graterThanOrEqualTo1)];

        yield [1, 1, self::toAbsolute($equalTo1)];
    }

    /**
     * @return string
     */
    protected static function getTempPath(): string
    {
        return dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
