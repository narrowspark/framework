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
use SplFileInfo;
use Viserio\Component\Finder\Comparator\DateComparator;

class DateRangeFilterIterator extends FilterIterator
{
    /** @var \Viserio\Component\Finder\Comparator\DateComparator[] */
    private $comparators;

    /**
     * Create a new DateRangeFilterIterator instance.
     *
     * @param Iterator<int|string, SplFileInfo>                     $iterator    The Iterator to filter
     * @param \Viserio\Component\Finder\Comparator\DateComparator[] $comparators An array of DateComparator instances
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
