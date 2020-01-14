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

use Viserio\Component\Finder\Filter\FileTypeFilterIterator;
use Viserio\Component\Finder\Tests\AbstractRealIteratorTestCase;

/**
 * @internal
 *
 * @small
 */
final class FileTypeFilterIteratorTest extends AbstractRealIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     *
     * @param int      $mode
     * @param string[] $expected
     */
    public function testAccept(int $mode, array $expected): void
    {
        $inner = new \Viserio\Component\Finder\Tests\Fixture\InnerTypeIterator(self::$files);
        $iterator = new FileTypeFilterIterator($inner, $mode);

        $this->assertIterator($expected, $iterator);
    }

    /**
     * @return iterable<array<int, array<string>|int|string>>
     */
    public static function provideAcceptCases(): iterable
    {
        $onlyFiles = [
            'test.py',
            'foo/bar.tmp',
            'test.php',
            '.bar',
            '.foo/.bar',
            '.foo/bar',
            'foo bar',
            'qux/baz_100_1.py',
            'qux/baz_1_2.py',
            'qux_0_1.php',
            'qux_1000_1.php',
            'qux_1002_0.php',
            'qux_10_2.php',
            'qux_12_0.php',
            'qux_2_0.php',
        ];

        $onlyDirectories = [
            '.git',
            'foo',
            'qux',
            'toto',
            'toto/.git',
            '.foo',
        ];

        yield [FileTypeFilterIterator::ONLY_FILES, self::toAbsolute($onlyFiles)];

        yield [FileTypeFilterIterator::ONLY_DIRECTORIES, self::toAbsolute($onlyDirectories)];
    }

    /**
     * @return string
     */
    protected static function getTempPath(): string
    {
        return dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
