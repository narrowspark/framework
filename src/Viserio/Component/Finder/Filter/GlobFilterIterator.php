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

namespace Viserio\Component\Finder\Filter;

use Iterator;
use SplFileInfo;
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
     * @param string                            $glob          the canonical glob
     * @param Iterator<int|string, SplFileInfo> $innerIterator the filtered iterator
     * @param int                               $mode          a bitwise combination of the mode constants
     *                                                         in {@link Glob}
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
