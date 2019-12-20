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

use FilterIterator;
use Iterator;

/**
 * SizeRangeFilterIterator filters out files that are not in the given size range.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SizeRangeFilterIterator extends FilterIterator
{
    private $comparators = [];

    /**
     * @param Iterator           $iterator    The Iterator to filter
     * @param NumberComparator[] $comparators An array of NumberComparator instances
     */
    public function __construct(Iterator $iterator, array $comparators)
    {
        $this->comparators = $comparators;

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return bool true if the value should be kept, false otherwise
     */
    public function accept()
    {
        $fileinfo = $this->current();

        if (! $fileinfo->isFile()) {
            return true;
        }

        $filesize = $fileinfo->getSize();

        foreach ($this->comparators as $compare) {
            if (! $compare->test($filesize)) {
                return false;
            }
        }

        return true;
    }
}
