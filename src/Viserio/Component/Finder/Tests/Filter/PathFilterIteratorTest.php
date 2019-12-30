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

use Iterator;
use Viserio\Component\Finder\Filter\PathFilterIterator;
use Viserio\Component\Finder\Tests\Fixture\MockFileListIterator;
use Viserio\Component\Finder\Tests\Fixture\MockSplFileInfo;
use Viserio\Component\Finder\Tests\IteratorTestCase;

/**
 * @internal
 *
 * @small
 */
final class PathFilterIteratorTest extends IteratorTestCase
{
    /**
     * @dataProvider provideFilterCases
     *
     * @param Iterator $inner
     * @param array    $matchPatterns
     * @param array    $noMatchPatterns
     * @param array    $resultArray
     */
    public function testFilter(Iterator $inner, array $matchPatterns, array $noMatchPatterns, array $resultArray): void
    {
        $iterator = new PathFilterIterator($inner, $matchPatterns, $noMatchPatterns);
        $this->assertIterator($resultArray, $iterator);
    }

    /**
     * @return iterable
     */
    public function provideFilterCases(): iterable
    {
        $inner = new MockFileListIterator();

        // PATH:   A/B/C/abc.dat
        $inner[] = new MockSplFileInfo([
            'name' => 'abc.dat',
            'subPathname' => 'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C' . \DIRECTORY_SEPARATOR . 'abc.dat',
        ]);

        // PATH:   A/B/ab.dat
        $inner[] = new MockSplFileInfo([
            'name' => 'ab.dat',
            'subPathname' => 'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'ab.dat',
        ]);

        // PATH:   A/a.dat
        $inner[] = new MockSplFileInfo([
            'name' => 'a.dat',
            'subPathname' => 'A' . \DIRECTORY_SEPARATOR . 'a.dat',
        ]);

        // PATH:   copy/A/B/C/abc.dat.copy
        $inner[] = new MockSplFileInfo([
            'name' => 'abc.dat.copy',
            'subPathname' => 'copy' . \DIRECTORY_SEPARATOR . 'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'C' . \DIRECTORY_SEPARATOR . 'abc.dat',
        ]);

        // PATH:   copy/A/B/ab.dat.copy
        $inner[] = new MockSplFileInfo([
            'name' => 'ab.dat.copy',
            'subPathname' => 'copy' . \DIRECTORY_SEPARATOR . 'A' . \DIRECTORY_SEPARATOR . 'B' . \DIRECTORY_SEPARATOR . 'ab.dat',
        ]);

        // PATH:   copy/A/a.dat.copy
        $inner[] = new MockSplFileInfo([
            'name' => 'a.dat.copy',
            'subPathname' => 'copy' . \DIRECTORY_SEPARATOR . 'A' . \DIRECTORY_SEPARATOR . 'a.dat',
        ]);

        yield [$inner, ['/^A/'],       [], ['abc.dat', 'ab.dat', 'a.dat']];

        yield [$inner, ['/^A\/B/'],    [], ['abc.dat', 'ab.dat']];

        yield [$inner, ['/^A\/B\/C/'], [], ['abc.dat']];

        yield [$inner, ['/A\/B\/C/'],  [], ['abc.dat', 'abc.dat.copy']];

        yield [$inner, ['A'],      [], ['abc.dat', 'ab.dat', 'a.dat']];

        yield [$inner, ['A/B'],    [], ['abc.dat', 'ab.dat']];

        yield [$inner, ['A/B/C'],  [], ['abc.dat']];

        yield [$inner, ['copy/A'],      [], ['abc.dat.copy', 'ab.dat.copy', 'a.dat.copy']];

        yield [$inner, ['copy/A/B'],    [], ['abc.dat.copy', 'ab.dat.copy']];

        yield [$inner, ['copy/A/B/C'],  [], ['abc.dat.copy']];
    }
}
