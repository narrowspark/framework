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

use RecursiveDirectoryIterator as BaseRecursiveDirectoryIterator;

/**
 * Recursive directory iterator that is working during recursive iteration.
 *
 * Based on the webmozart glob package
 *
 * @see https://github.com/webmozart/glob/blob/master/src/Iterator/RecursiveDirectoryIterator.php
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 *
 * @internal
 */
final class RecursiveDirectoryIterator extends BaseRecursiveDirectoryIterator
{
    /** @var bool */
    private $normalizeKey;

    /** @var bool */
    private $normalizeCurrent;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $path, int $flags = 0)
    {
        parent::__construct($path, $flags);

        // Normalize slashes on Windows
        $this->normalizeKey = '\\' === \DIRECTORY_SEPARATOR && ($flags & self::KEY_AS_FILENAME) === 0;
        $this->normalizeCurrent = '\\' === \DIRECTORY_SEPARATOR && ($flags & self::CURRENT_AS_PATHNAME) !== 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren(): object
    {
        return new self($this->getPathname(), $this->getFlags());
    }

    /**
     * {@inheritdoc}
     */
    public function key(): string
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
    public function current(): string
    {
        $current = parent::current();

        if ($this->normalizeCurrent) {
            $current = \str_replace('\\', '/', $current);
        }

        return $current;
    }
}
