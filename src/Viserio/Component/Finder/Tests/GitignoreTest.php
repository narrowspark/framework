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

namespace Viserio\Component\Finder\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Finder\Gitignore;

/**
 * @internal
 *
 * @small
 */
final class GitignoreTest extends TestCase
{
    /**
     * @dataProvider provideCasesCases
     *
     * @param string   $patterns
     * @param string[] $matchingCases
     * @param string[] $nonMatchingCases
     */
    public function testCases(string $patterns, array $matchingCases, array $nonMatchingCases): void
    {
        $regex = Gitignore::toRegex($patterns);

        foreach ($matchingCases as $matchingCase) {
            self::assertRegExp($regex, $matchingCase, \sprintf('Failed asserting path [%s] matches gitignore patterns [%s] using regex [%s]', $matchingCase, $patterns, $regex));
        }

        foreach ($nonMatchingCases as $nonMatchingCase) {
            self::assertNotRegExp($regex, $nonMatchingCase, \sprintf('Failed asserting path [%s] not matching gitignore patterns [%s] using regex [%s]', $nonMatchingCase, $patterns, $regex));
        }
    }

    /**
     * @return array<array<array<string>|string>> return is array of
     *                                            [
     *                                            [
     *                                            '', // Git-ignore Pattern
     *                                            [], // array of file paths matching
     *                                            [], // array of file paths not matching
     *                                            ],
     *                                            ]
     */
    public function provideCasesCases(): iterable
    {
        return [
            [
                '
                    *
                    !/bin/bash
                ',
                ['bin/cat', 'abc/bin/cat'],
                ['bin/bash'],
            ],
            [
                'fi#le.txt',
                [],
                ['#file.txt'],
            ],
            [
                '
                /bin/
                /usr/local/
                !/bin/bash
                !/usr/local/bin/bash
                ',
                ['bin/cat'],
                ['bin/bash'],
            ],
            [
                '*.py[co]',
                ['file.pyc', 'file.pyc'],
                ['filexpyc', 'file.pycx', 'file.py'],
            ],
            [
                'dir1/**/dir2/',
                ['dir1/dirA/dir2/', 'dir1/dirA/dirB/dir2/'],
                [],
            ],
            [
                'dir1/*/dir2/',
                ['dir1/dirA/dir2/'],
                ['dir1/dirA/dirB/dir2/'],
            ],
            [
                '/*.php',
                ['file.php'],
                ['app/file.php'],
            ],
            [
                '\#file.txt',
                ['#file.txt'],
                [],
            ],
            [
                '*.php',
                ['app/file.php', 'file.php'],
                ['file.phps', 'file.phps', 'filephps'],
            ],
            [
                'app/cache/',
                ['app/cache/file.txt', 'app/cache/dir1/dir2/file.txt', 'a/app/cache/file.txt'],
                [],
            ],
            [
                '
                #IamComment
                /app/cache/',
                ['app/cache/file.txt', 'app/cache/subdir/ile.txt'],
                ['a/app/cache/file.txt', '#IamComment', 'IamComment'],
            ],
            [
                '
                /app/cache/
                #LastLineIsComment',
                ['app/cache/file.txt', 'app/cache/subdir/ile.txt'],
                ['a/app/cache/file.txt', '#LastLineIsComment', 'LastLineIsComment'],
            ],
            [
                '
                /app/cache/
                \#file.txt
                #LastLineIsComment',
                ['app/cache/file.txt', 'app/cache/subdir/ile.txt', '#file.txt'],
                ['a/app/cache/file.txt', '#LastLineIsComment', 'LastLineIsComment'],
            ],
            [
                '
                /app/cache/
                \#file.txt
                #IamComment
                another_file.txt',
                ['app/cache/file.txt', 'app/cache/subdir/ile.txt', '#file.txt', 'another_file.txt'],
                ['a/app/cache/file.txt', 'IamComment', '#IamComment'],
            ],
        ];
    }
}
