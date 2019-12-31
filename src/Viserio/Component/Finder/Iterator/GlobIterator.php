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

namespace Viserio\Component\Finder\Iterator;

use ArrayIterator;
use EmptyIterator;
use Iterator;
use IteratorIterator;
use RecursiveIteratorIterator;
use Viserio\Component\Finder\Filter\GlobFilterIterator;
use Viserio\Component\Finder\SplFileInfo;
use Viserio\Component\Finder\Util;

/**
 * Returns filesystem paths matching a glob.
 *
 * Based on the webmozart glob package
 *
 * @see https://github.com/webmozart/glob/blob/master/src/Glob.php
 * @see https://github.com/webmozart/glob/blob/master/src/Iterator/GlobIterator.php
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GlobIterator extends IteratorIterator
{
    /**
     * Create a new GlobIterator instance.
     *
     * @param string $glob  the glob pattern
     * @param int    $flags a bitwise combination of the flag constants
     */
    public function __construct(string $glob, int $flags = 0)
    {
        if (\file_exists($glob) && ! (\strpos($glob, '*') !== false || \strpos($glob, '{') !== false || \strpos($glob, '?') !== false || \strpos($glob, '[') !== false)) {
            // If the glob is a file path, return that path
            $innerIterator = new ArrayIterator([$glob]);
        } elseif (\is_dir($basePath = Util::getBasePath($glob))) {
            // Use the system's much more efficient glob() function where we can
            if (
                // glob() does not support /**/
                \strpos($glob, '/**/') === false
                // glob() does not support stream wrappers
                && \strpos($glob, '://') === false
                // glob() does not support [^...] on Windows
                && (\PHP_OS_FAMILY !== 'Windows' || \strpos($glob, '[^') === false)
            ) {
                if (\defined('GLOB_BRACE')) {
                    $results = \glob($glob, $flags | \GLOB_NOSORT | \GLOB_BRACE);
                } else {
                    $results = Util::polyfillGlobBrace($glob, $flags | \GLOB_NOSORT);
                }

                // $results may be empty or false if $glob is invalid
                if ($results === false || \count($results) === 0) {
                    // Parse glob and provoke errors if invalid
                    Util::toRegEx($glob);

                    // Otherwise return empty result set
                    $innerIterator = new EmptyIterator();
                } else {
                    $innerIterator = new ArrayIterator($results);
                }
            } else {
                // Otherwise scan the glob's base directory for matches
                $innerIterator = new GlobFilterIterator(
                    $glob,
                    new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator(
                            $basePath,
                            RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
                            | RecursiveDirectoryIterator::SKIP_DOTS
                        ),
                        RecursiveIteratorIterator::SELF_FIRST
                    ),
                    GlobFilterIterator::FILTER_VALUE
                );

                $innerIterator = $this->map(static function (SplFileInfo $file): string {
                    return $file->getNormalizedPathname();
                }, $innerIterator);
            }
        } else {
            // If the glob's base directory does not exist, return nothing
            $innerIterator = new EmptyIterator();
        }

        parent::__construct($innerIterator);
    }

    /**
     * Applies a mapping function to all values of an iterator.
     *
     * The function is passed the current iterator value and should return a
     * modified iterator value. The key is left as-is and not passed to the mapping
     * function.
     *
     * @param callable                    $function Mapping function: mixed function(mixed $value)
     * @param iterable<int|string, mixed> $iterable Iterable to be mapped over
     *
     * @return Iterator<int|string, mixed>
     */
    private function map(callable $function, iterable $iterable): Iterator
    {
        foreach ($iterable as $key => $value) {
            yield $key => $function($value);
        }
    }
}
