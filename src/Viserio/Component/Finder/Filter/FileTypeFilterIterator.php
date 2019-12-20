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

/**
 * FileTypeFilterIterator only keeps files, directories, or both.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FileTypeFilterIterator extends FilterIterator
{
    public const ONLY_FILES = 1;
    public const ONLY_DIRECTORIES = 2;

    private $mode;

    /**
     * @param Iterator $iterator The Iterator to filter
     * @param int      $mode     The mode (self::ONLY_FILES or self::ONLY_DIRECTORIES)
     */
    public function __construct(Iterator $iterator, int $mode)
    {
        $this->mode = $mode;

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

        if (self::ONLY_DIRECTORIES === (self::ONLY_DIRECTORIES & $this->mode) && $fileinfo->isFile()) {
            return false;
        }

        if (self::ONLY_FILES === (self::ONLY_FILES & $this->mode) && $fileinfo->isDir()) {
            return false;
        }

        return true;
    }
}
