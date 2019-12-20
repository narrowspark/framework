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
use Viserio\Component\Finder\Comparator\DateComparator;

/**
 * DateRangeFilterIterator filters out files that are not in the given date range (last modified dates).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DateRangeFilterIterator extends FilterIterator
{
    private $comparators = [];

    /**
     * @param Iterator                                              $iterator    The Iterator to filter
     * @param \Viserio\Component\Finder\Comparator\DateComparator[] $comparators An array of DateComparator instances
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
    public function accept(): bool
    {
        $fileinfo = $this->current();

        if (! file_exists($fileinfo->getPathname())) {
            return false;
        }

        foreach ($this->comparators as $compare) {
            if ($compare->getTimeType() === DateComparator::LAST_ACCESSED) {
                $filedate = $fileinfo->getATime();
            } elseif ($compare->getTimeType() === DateComparator::LAST_CHANGED) {
                $filedate = $fileinfo->getCTime();
            } else {
                $filedate = $fileinfo->getMTime();
            }

            if (! $compare->test($filedate)) {
                return false;
            }
        }

        return true;
    }
}
