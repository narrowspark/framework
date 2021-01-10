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

/**
 * SizeRangeFilterIterator filters out files that are not in the given size range.
 *
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Iterator/SizeRangeFilterIterator.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SizeRangeFilterIterator extends FilterIterator
{
    /** @var \Viserio\Component\Finder\Comparator\NumberComparator[] */
    private $comparators;

    /**
     * Create a new SizeRangeFilterIterator instance.
     *
     * @param Iterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator    The Iterator to filter
     * @param \Viserio\Component\Finder\Comparator\NumberComparator[]    $comparators An array of NumberComparator instances
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
