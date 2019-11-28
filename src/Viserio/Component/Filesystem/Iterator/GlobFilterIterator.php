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

namespace Viserio\Component\Filesystem\Iterator;

use Iterator;

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
     * Creates a new GlobFilterIterator instance.
     *
     * @param string   $glob          the canonical glob
     * @param Iterator $innerIterator the filtered iterator
     * @param int      $mode          a bitwise combination of the mode constants
     * @param int      $flags         a bitwise combination of the flag constants
     *                                in {@link Glob}
     */
    public function __construct($glob, Iterator $innerIterator, $mode = self::FILTER_VALUE, $flags = 0)
    {
        parent::__construct(
            GlobIterator::toRegEx($glob, $flags),
            GlobIterator::getStaticPrefix($glob, $flags),
            $innerIterator,
            $mode
        );
    }
}
