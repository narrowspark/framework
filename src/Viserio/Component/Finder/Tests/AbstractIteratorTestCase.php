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

namespace Viserio\Component\Finder\Tests;

use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Traversable;

/**
 * @internal
 */
abstract class AbstractIteratorTestCase extends TestCase
{
    /**
     * @param array<int, false|string>                                      $expected
     * @param Traversable<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator
     */
    protected function assertIterator(array $expected, Traversable $iterator): void
    {
        // set iterator_to_array $use_key to false to avoid values merge
        // this made FinderTest::testAppendWithAnArray() fail with GnuFinderAdapter
        $values = \array_map(static function (SplFileInfo $fileinfo): string {
            return \str_replace('/', \DIRECTORY_SEPARATOR, $fileinfo->getPathname());
        }, \iterator_to_array($iterator, false));

        $expected = \array_map(static function (string $path): string {
            return \str_replace('/', \DIRECTORY_SEPARATOR, $path);
        }, $expected);

        \sort($values);
        \sort($expected);

        self::assertEquals($expected, \array_values($values));
    }

    /**
     * @param string[]                                                      $expected
     * @param Traversable<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator
     */
    protected function assertOrderedIterator(array $expected, Traversable $iterator): void
    {
        $values = \array_map(static function (SplFileInfo $fileinfo): string {
            return $fileinfo->getPathname();
        }, \iterator_to_array($iterator));

        self::assertEquals($expected, \array_values($values));
    }

    /**
     *  Same as assertOrderedIterator, but checks the order of groups of
     *  array elements.
     *
     *  @param array<array<string>|string> $expected - an array of arrays. For any two subarrays
     *      $a and $b such that $a goes before $b in $expected, the method
     *      asserts that any element of $a goes before any element of $b
     *      in the sequence generated by $iterator
     * @param Traversable<string, SplFileInfo> $iterator
     */
    protected function assertOrderedIteratorForGroups(array $expected, Traversable $iterator): void
    {
        $values = \array_values(\array_map(static function (SplFileInfo $fileinfo): string {
            return $fileinfo->getPathname();
        }, \iterator_to_array($iterator)));

        foreach ($expected as $subarray) {
            $temp = [];

            while (\count($values) !== 0 && \count($temp) < \count((array) $subarray)) {
                $temp[] = \array_shift($values);
            }

            $subarray = (array) $subarray;

            \sort($temp);
            \sort($subarray);

            self::assertEquals($subarray, $temp);
        }
    }

    /**
     * Same as AbstractIteratorTestCase::assertIterator with foreach usage.
     *
     * @param string[]                         $expected
     * @param Traversable<string, SplFileInfo> $iterator
     */
    protected function assertIteratorInForeach(array $expected, Traversable $iterator): void
    {
        $values = [];

        foreach ($iterator as $file) {
            self::assertInstanceOf(SplFileInfo::class, $file);

            $values[] = $file->getPathname();
        }

        \sort($values);
        \sort($expected);

        self::assertEquals($expected, \array_values($values));
    }

    /**
     * Same as AbstractIteratorTestCase::assertOrderedIterator with foreach usage.
     *
     * @param string[]                         $expected
     * @param Traversable<string, SplFileInfo> $iterator
     */
    protected function assertOrderedIteratorInForeach(array $expected, Traversable $iterator): void
    {
        $values = [];

        foreach ($iterator as $file) {
            self::assertInstanceOf(SplFileInfo::class, $file);

            $values[] = $file->getPathname();
        }

        self::assertEquals($expected, \array_values($values));
    }
}
