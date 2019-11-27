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

/**
 * Recursive directory iterator that is working during recursive iteration.
 *
 * Based on the webmozart glob package
 *
 * @see https://github.com/webmozart/glob/blob/master/src/Iterator/RecursiveDirectoryIterator.php
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    /** @var bool */
    private $normalizeKey;

    /** @var bool */
    private $normalizeCurrent;

    /**
     * {@inheritdoc}
     */
    public function __construct($path, $flags = 0)
    {
        parent::__construct($path, $flags);

        // Normalize slashes on Windows
        $this->normalizeKey = '\\' === \DIRECTORY_SEPARATOR && ! ($flags & self::KEY_AS_FILENAME);
        $this->normalizeCurrent = '\\' === \DIRECTORY_SEPARATOR && ($flags & self::CURRENT_AS_PATHNAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        return new static($this->getPathname(), $this->getFlags());
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        $key = parent::key();

        if ($this->normalizeKey) {
            $key = \str_replace('\\', '/', $key);
        }

        return $key;
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        $current = parent::current();

        if ($this->normalizeCurrent) {
            $current = \str_replace('\\', '/', $current);
        }

        return $current;
    }
}
