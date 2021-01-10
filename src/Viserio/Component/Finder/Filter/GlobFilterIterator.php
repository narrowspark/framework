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

namespace Viserio\Component\Finder\Filter;

use Iterator;
use Viserio\Component\Finder\Util;

/**
 * Filters an iterator by a glob.
 *
 * Based on the webmozart glob package
 *
 * @see https://github.com/webmozart/glob/blob/master/src/Iterator/GlobFilterIterator.php
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GlobFilterIterator extends RegexFilterIterator
{
    /**
     * Create a new GlobFilterIterator instance.
     *
     * @param string                                                     $glob          the canonical glob
     * @param Iterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $innerIterator the filtered iterator
     * @param int                                                        $mode          a bitwise combination of the mode constants
     *                                                                                  in {@link Glob}
     */
    public function __construct(string $glob, Iterator $innerIterator, int $mode = self::FILTER_VALUE)
    {
        parent::__construct(
            Util::toRegEx($glob),
            Util::getStaticPrefix($glob),
            $innerIterator,
            $mode
        );
    }
}
