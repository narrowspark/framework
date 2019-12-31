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
use SplFileInfo;
use Viserio\Component\Finder\Filter\FileContentFilterIterator;
use Viserio\Component\Finder\Tests\AbstractIteratorTestCase;
use Viserio\Component\Finder\Tests\Fixture\MockFileListIterator;
use Viserio\Component\Finder\Tests\Fixture\MockSplFileInfo;

/**
 * @internal
 *
 * @small
 */
final class FileContentFilterIteratorTest extends AbstractIteratorTestCase
{
    public function testAccept(): void
    {
        $inner = new MockFileListIterator(['test.txt']);
        $iterator = new FilecontentFilterIterator($inner, [], []);

        $this->assertIterator(['test.txt'], $iterator);
    }

    public function testDirectory(): void
    {
        $inner = new MockFileListIterator(['directory']);
        $iterator = new FilecontentFilterIterator($inner, ['directory'], []);

        $this->assertIterator([], $iterator);
    }

    public function testUnreadableFile(): void
    {
        $inner = new MockFileListIterator(['file r-']);
        $iterator = new FilecontentFilterIterator($inner, ['file r-'], []);

        $this->assertIterator([], $iterator);
    }

    /**
     * @dataProvider provideFilterCases
     *
     * @param Iterator<string, SplFileInfo> $inner
     * @param string[]                      $matchPatterns
     * @param string[]                      $noMatchPatterns
     * @param string[]                      $resultArray
     */
    public function testFilter(Iterator $inner, array $matchPatterns, array $noMatchPatterns, array $resultArray): void
    {
        $iterator = new FilecontentFilterIterator($inner, $matchPatterns, $noMatchPatterns);

        $this->assertIterator($resultArray, $iterator);
    }

    /**
     * @return iterable<array<int, array<int, string>|\ArrayIterator<string, string>>>>
     */
    public function provideFilterCases(): iterable
    {
        $inner = new MockFileListIterator();

        $inner[] = new MockSplFileInfo(
            [
                'name' => 'a.txt',
                'contents' => 'Lorem ipsum...',
                'type' => 'file',
                'mode' => 'r+', ]
        );
        $inner[] = new MockSplFileInfo(
            [
                'name' => 'b.yml',
                'contents' => 'dolor sit...',
                'type' => 'file',
                'mode' => 'r+', ]
        );
        $inner[] = new MockSplFileInfo(
            [
                'name' => 'some/other/dir/third.php',
                'contents' => 'amet...',
                'type' => 'file',
                'mode' => 'r+', ]
        );
        $inner[] = new MockSplFileInfo(
            [
                'name' => 'unreadable-file.txt',
                'contents' => false,
                'type' => 'file',
                'mode' => 'r+', ]
        );

        yield [$inner, ['.'], [], ['a.txt', 'b.yml', 'some/other/dir/third.php']];

        yield [$inner, ['ipsum'], [], ['a.txt']];

        yield [$inner, ['i', 'amet'], ['Lorem', 'amet'], ['b.yml']];
    }
}
