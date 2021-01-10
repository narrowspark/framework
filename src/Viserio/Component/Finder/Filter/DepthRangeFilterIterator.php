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
use RecursiveIteratorIterator;

/**
 * DepthRangeFilterIterator limits the directory depth.
 *
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Iterator/DepthRangeFilterIterator.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DepthRangeFilterIterator extends FilterIterator
{
    /** @var int */
    private $minDepth;

    /**
     * @param RecursiveIteratorIterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator The Iterator to filter
     * @param int                                                                         $minDepth The min depth
     * @param int                                                                         $maxDepth The max depth
     */
    public function __construct(RecursiveIteratorIterator $iterator, int $minDepth = 0, int $maxDepth = \PHP_INT_MAX)
    {
        $this->minDepth = $minDepth;

        $iterator->setMaxDepth(\PHP_INT_MAX === $maxDepth ? -1 : $maxDepth);

        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        /** @var RecursiveIteratorIterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator */
        $iterator = $this->getInnerIterator();

        return $iterator->getDepth() >= $this->minDepth;
    }
}
