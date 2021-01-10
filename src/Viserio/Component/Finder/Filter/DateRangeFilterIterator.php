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

use FilterIterator;
use Iterator;
use Viserio\Component\Finder\Comparator\DateComparator;

class DateRangeFilterIterator extends FilterIterator
{
    /** @var \Viserio\Component\Finder\Comparator\DateComparator[] */
    private $comparators;

    /**
     * Create a new DateRangeFilterIterator instance.
     *
     * @param Iterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator    The Iterator to filter
     * @param \Viserio\Component\Finder\Comparator\DateComparator[]      $comparators An array of DateComparator instances
     */
    public function __construct(Iterator $iterator, array $comparators)
    {
        $this->comparators = $comparators;

        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
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
