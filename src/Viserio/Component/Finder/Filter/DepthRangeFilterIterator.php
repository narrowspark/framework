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
use RecursiveIteratorIterator;

/**
 * DepthRangeFilterIterator limits the directory depth.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DepthRangeFilterIterator extends FilterIterator
{
    /** @var int */
    private $minDepth;

    /**
     * @param RecursiveIteratorIterator $iterator The Iterator to filter
     * @param int                       $minDepth The min depth
     * @param int                       $maxDepth The max depth
     */
    public function __construct(RecursiveIteratorIterator $iterator, int $minDepth = 0, int $maxDepth = \PHP_INT_MAX)
    {
        $this->minDepth = $minDepth;

        $iterator->setMaxDepth(\PHP_INT_MAX === $maxDepth ? -1 : $maxDepth);

        parent::__construct($iterator);
    }

    /**
     * Filters the iterator values.
     *
     * @return bool true if the value should be kept, false otherwise
     */
    public function accept(): bool
    {
        return $this->getInnerIterator()->getDepth() >= $this->minDepth;
    }
}
