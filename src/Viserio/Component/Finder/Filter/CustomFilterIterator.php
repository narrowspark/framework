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
 * CustomFilterIterator filters files by applying anonymous functions.
 *
 * The anonymous function receives a \SplFileInfo and must return false
 * to remove files.
 *
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Iterator/CustomFilterIterator.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CustomFilterIterator extends FilterIterator
{
    /** @var callable[] */
    private $filters;

    /**
     * Create a new CustomFilterIterator instance.
     *
     * @param Iterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator
     * @param callable                                                   ...$filters
     */
    public function __construct(Iterator $iterator, callable ...$filters)
    {
        $this->filters = $filters;

        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        $fileinfo = $this->current();

        foreach ($this->filters as $filter) {
            if ($filter($fileinfo) === false) {
                return false;
            }
        }

        return true;
    }
}
