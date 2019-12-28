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
use RecursiveIteratorIterator;
use Viserio\Component\Finder\Filter\ExcludeDirectoryFilterIterator;
use Viserio\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Viserio\Component\Finder\Tests\RealIteratorTestCase;

/**
 * @internal
 *
 * @small
 */
final class ExcludeDirectoryFilterIteratorTest extends RealIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     *
     * @param mixed $directories
     * @param mixed $expected
     */
    public function testAccept($directories, $expected): void
    {
        $inner = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::toAbsolute(), FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);

        $iterator = new ExcludeDirectoryFilterIterator($inner, $directories);

        $this->assertIterator($expected, $iterator);
    }

    public function provideAcceptCases(): iterable
    {
        $foo = [
            '.gitignore',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'atime.php',
            'test.py',
            'test.php',
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
        ];

        $fo = [
            '.gitignore',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'atime.php',
            'test.py',
            'foo',
            'foo/bar.tmp',
            'test.php',
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
        ];

        $toto = [
            '.gitignore',
            '.bar',
            '.foo',
            '.foo/.bar',
            '.foo/bar',
            '.git',
            'atime.php',
            'test.py',
            'foo',
            'foo/bar.tmp',
            'test.php',
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
        ];

        yield [['foo'], self::toAbsolute($foo)];

        yield [['fo'], self::toAbsolute($fo)];

        yield [['toto/'], self::toAbsolute($toto)];
    }

    /**
     * @return string
     */
    protected static function getTempPath(): string
    {
        return dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
