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
use SplFileInfo;

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
     * @param RecursiveIteratorIterator<int|string, SplFileInfo> $iterator The Iterator to filter
     * @param int                                                $minDepth The min depth
     * @param int                                                $maxDepth The max depth
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
        /** @var RecursiveIteratorIterator<int|string, SplFileInfo> $iterator */
        $iterator = $this->getInnerIterator();

        return $iterator->getDepth() >= $this->minDepth;
    }
}
