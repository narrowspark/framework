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

namespace Viserio\Component\Filesystem\Iterator;

use FilterIterator;
use Iterator;
use SplFileInfo;

class SplFileInfoMethodFilterIterator extends FilterIterator
{
    /** @var string */
    private $method;

    /**
     * Create a new SplFileInfoMethodFilterIterator instance.
     *
     * @param Iterator<string, SplFileInfo> $iterator
     */
    public function __construct(Iterator $iterator, string $method)
    {
        parent::__construct($iterator);

        $this->method = $method;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        /** @var SplFileInfo $file */
        $file = $this->getInnerIterator()->current();

        if (! $file->{$this->method}()) {
            return false;
        }

        return true;
    }
}
