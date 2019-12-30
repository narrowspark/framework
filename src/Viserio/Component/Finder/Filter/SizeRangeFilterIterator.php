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
     * @param Iterator<int|string, SplFileInfo>                       $iterator    The Iterator to filter
     * @param \Viserio\Component\Finder\Comparator\NumberComparator[] $comparators An array of NumberComparator instances
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
