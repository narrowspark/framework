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
     * @param string                        $method
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
