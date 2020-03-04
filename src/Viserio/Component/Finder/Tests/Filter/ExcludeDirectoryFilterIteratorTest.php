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

use FilesystemIterator;
use RecursiveIteratorIterator;
use Viserio\Component\Finder\Filter\ExcludeDirectoryFilterIterator;
use Viserio\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Viserio\Component\Finder\Tests\AbstractRealIteratorTestCase;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ExcludeDirectoryFilterIteratorTest extends AbstractRealIteratorTestCase
{
    /**
     * @dataProvider provideAcceptCases
     *
     * @param string[] $directories
     * @param string[] $expected
     */
    public function testAccept(array $directories, array $expected): void
    {
        /** @var string $path */
        $path = self::toAbsolute();

        $inner = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
        $iterator = new ExcludeDirectoryFilterIterator($inner, $directories);

        $this->assertIterator($expected, $iterator);
    }

    /**
     * @return iterable<array<array<string>|string>>
     */
    public static function provideAcceptCases(): iterable
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

    protected static function getTempPath(): string
    {
        return dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'viserio_finder';
    }
}
