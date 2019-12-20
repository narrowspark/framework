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
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * CustomFilterIterator filters files by applying anonymous functions.
 *
 * The anonymous function receives a \SplFileInfo and must return false
 * to remove files.
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
     * @param Iterator   $iterator The Iterator to filter
     * @param callable[] $filters
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException
     */
    public function __construct(Iterator $iterator, array $filters)
    {
        foreach ($filters as $filter) {
            if (! \is_callable($filter)) {
                throw new InvalidArgumentException('Invalid PHP callback.');
            }
        }

        $this->filters = $filters;

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

        foreach ($this->filters as $filter) {
            if ($filter($fileinfo) === false) {
                return false;
            }
        }

        return true;
    }
}
