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
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Iterator/FileTypeFilterIterator.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FileTypeFilterIterator extends FilterIterator
{
    /** @var int */
    public const ONLY_FILES = 1;

    /** @var int */
    public const ONLY_DIRECTORIES = 2;

    /** @var int */
    private $mode;

    /**
     * Create a new FileTypeFilterIterator instance.
     *
     * @param Iterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator The Iterator to filter
     * @param int                                                        $mode     The mode (self::ONLY_FILES or self::ONLY_DIRECTORIES)
     */
    public function __construct(Iterator $iterator, int $mode)
    {
        $this->mode = $mode;

        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
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
