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

use ArrayIterator;
use SplFileInfo;
use Viserio\Component\Finder\Filter\FileTypeFilterIterator;
use Viserio\Component\Finder\Tests\RealIteratorTestCase;

/**
 * @internal
 *
 * @small
 */
final class FileTypeFilterIteratorTest extends RealIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     *
     * @param mixed $mode
     * @param mixed $expected
     */
    public function testAccept($mode, $expected): void
    {
        $inner = new InnerTypeIterator(self::$files);

        $iterator = new FileTypeFilterIterator($inner, $mode);

        $this->assertIterator($expected, $iterator);
    }

    /**
     * @return iterable
     */
    public function provideAcceptCases(): iterable
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

class InnerTypeIterator extends ArrayIterator
{
    public function current()
    {
        return new SplFileInfo(parent::current());
    }

    public function isFile()
    {
        return $this->current()->isFile();
    }

    public function isDir()
    {
        return $this->current()->isDir();
    }
}
