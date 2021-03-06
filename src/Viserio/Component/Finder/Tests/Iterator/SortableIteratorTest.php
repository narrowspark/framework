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

namespace Viserio\Component\Finder\Tests\Iterator;

use SplFileInfo;
use Viserio\Component\Finder\Iterator\SortableIterator;
use Viserio\Component\Finder\Tests\AbstractRealIteratorTestCase;
use Viserio\Component\Finder\Tests\Fixture\Iterator;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class SortableIteratorTest extends AbstractRealIteratorTestCase
{
    public function testConstructor(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new SortableIterator(new Iterator([]), 'foobar');
    }

    /**
     * @dataProvider provideAcceptCases
     *
     * @param mixed $mode
     * @param mixed $expected
     */
    public function testAccept($mode, $expected): void
    {
        if (! \is_callable($mode)) {
            switch ($mode) {
                case SortableIterator::SORT_BY_ACCESSED_TIME:
                    /** @var string $gitPath */
                    $gitPath = self::toAbsolute('.git');

                    \touch($gitPath);

                    \sleep(1);

                    /** @var string $barPath */
                    $barPath = self::toAbsolute('.bar');

                    \file_get_contents($barPath);

                    break;
                case SortableIterator::SORT_BY_CHANGED_TIME:
                case SortableIterator::SORT_BY_MODIFIED_TIME:
                    /** @var string $testPhp */
                    $testPhp = self::toAbsolute('test.php');

                    \file_put_contents($testPhp, 'foo');

                    \sleep(1);

                    /** @var string $testPy */
                    $testPy = self::toAbsolute('test.py');

                    \file_put_contents($testPy, 'foo');

                    break;
            }
        }

        $iterator = new SortableIterator(new Iterator(self::$files), $mode);

        if (SortableIterator::SORT_BY_ACCESSED_TIME === $mode
            || SortableIterator::SORT_BY_CHANGED_TIME === $mode
            || SortableIterator::SORT_BY_MODIFIED_TIME === $mode
        ) {
            if (\PHP_OS_FAMILY === 'Windows' && SortableIterator::SORT_BY_MODIFIED_TIME !== $mode) {
                self::markTestSkipped('Sorting by atime or ctime is not supported on Windows');
            }
            $this->assertOrderedIteratorForGroups($expected, $iterator);
        } else {
            $this->assertOrderedIterator($expected, $iterator);
        }
    }

    /**
     * @return iterable<array<int, array<string>|(Closure(SplFileInfo, SplFileInfo): int)|string>>
     */
    public static function provideAcceptCases(): iterable
    {
        self::$tmpDir = self::getTempPath();

        $sortByName = [
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'foo',
            'foo bar',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
            'toto/.git',
        ];

        $sortByType = [
            '.foo',
            '.git',
            'foo',
            'qux',
            'toto',
            'toto/.git',
            '.bar',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
            'foo/bar.tmp',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
        ];

        $sortByAccessedTime = [
            // For these two files the access time was set to 2005-10-15
            ['foo/bar.tmp', 'test.php'],
            // These files were created more or less at the same time
            [
                '.git',
                '.foo',
                '.foo/.bar',
                '.foo/bar',
                'test.py',
                'foo',
                'toto',
                'toto/.git',
                'foo bar',
                'qux',
                'qux/baz_100_1.py',
                'qux/baz_1_2.py',
                'qux_0_1.php',
                'qux_1000_1.php',
                'qux_1002_0.php',
                'qux_10_2.php',
                'qux_12_0.php',
                'qux_2_0.php',
            ],
            // This file was accessed after sleeping for 1 sec
            ['.bar'],
        ];

        $sortByChangedTime = [
            [
                '.git',
                '.foo',
                '.foo/.bar',
                '.foo/bar',
                '.bar',
                'foo',
                'foo/bar.tmp',
                'toto',
                'toto/.git',
                'foo bar',
                'qux',
                'qux/baz_100_1.py',
                'qux/baz_1_2.py',
                'qux_0_1.php',
                'qux_1000_1.php',
                'qux_1002_0.php',
                'qux_10_2.php',
                'qux_12_0.php',
                'qux_2_0.php',
            ],
            ['test.php'],
            ['test.py'],
        ];

        $sortByModifiedTime = [
            [
                '.git',
                '.foo',
                '.foo/.bar',
                '.foo/bar',
                '.bar',
                'foo',
                'foo/bar.tmp',
                'toto',
                'toto/.git',
                'foo bar',
                'qux',
                'qux/baz_100_1.py',
                'qux/baz_1_2.py',
                'qux_0_1.php',
                'qux_1000_1.php',
                'qux_1002_0.php',
                'qux_10_2.php',
                'qux_12_0.php',
                'qux_2_0.php',
            ],
            ['test.php'],
            ['test.py'],
        ];

        $sortByNameNatural = [
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'foo',
            'foo/bar.tmp',
            'foo bar',
            'qux',
            'qux/baz_1_2.py',
            'qux/baz_100_1.py',
            'qux_0_1.php',
            'qux_2_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'test.php',
            'test.py',
            'toto',
            'toto/.git',
        ];

        $customComparison = [
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'foo',
            'foo bar',
            'foo/bar.tmp',
            'qux',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
            'test.php',
            'test.py',
            'toto',
            'toto/.git',
        ];

        yield [SortableIterator::SORT_BY_NAME, self::toAbsolute($sortByName)];

        yield [SortableIterator::SORT_BY_TYPE, self::toAbsolute($sortByType)];

        yield [SortableIterator::SORT_BY_ACCESSED_TIME, self::toAbsolute($sortByAccessedTime)];

        if (\PHP_OS_FAMILY !== 'Windows') {
            yield [SortableIterator::SORT_BY_CHANGED_TIME, self::toAbsolute($sortByChangedTime)];
        }

        yield [SortableIterator::SORT_BY_MODIFIED_TIME, self::toAbsolute($sortByModifiedTime)];

        yield [SortableIterator::SORT_BY_NAME_NATURAL, self::toAbsolute($sortByNameNatural)];

        yield [static function (SplFileInfo $a, SplFileInfo $b): int {
            return \strcmp((string) $a->getRealPath(), (string) $b->getRealPath());
        }, self::toAbsolute($customComparison)];
    }

    /**
     * @return string
     */
    protected static function getTempPath(): string
    {
        return \realpath(\sys_get_temp_dir()) . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
