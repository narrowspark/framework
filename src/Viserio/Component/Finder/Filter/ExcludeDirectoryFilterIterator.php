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
use RecursiveIterator;

/**
 * ExcludeDirectoryFilterIterator filters out directories.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExcludeDirectoryFilterIterator extends FilterIterator implements RecursiveIterator
{
    private $iterator;

    private $isRecursive;

    private $excludedDirs = [];

    private $excludedPattern;

    /**
     * @param Iterator $iterator    The Iterator to filter
     * @param string[] $directories An array of directories to exclude
     */
    public function __construct(Iterator $iterator, array $directories)
    {
        $this->iterator = $iterator;
        $this->isRecursive = $iterator instanceof RecursiveIterator;
        $patterns = [];

        foreach ($directories as $directory) {
            $directory = \rtrim($directory, '/');

            if (! $this->isRecursive || false !== \strpos($directory, '/')) {
                $patterns[] = \preg_quote($directory, '#');
            } else {
                $this->excludedDirs[$directory] = true;
            }
        }

        if ($patterns) {
            $this->excludedPattern = '#(?:^|/)(?:' . \implode('|', $patterns) . ')(?:/|$)#';
        }

        parent::__construct($iterator);
    }

    /**
     * {@inheritdoc}
     */
    public function accept(): bool
    {
        if ($this->isRecursive && isset($this->excludedDirs[$this->getFilename()]) && $this->isDir()) {
            return false;
        }

        if ($this->excludedPattern) {
            $path = $this->isDir() ? $this->current()->getSubPathname() : $this->current()->getRelativePath();

            return ! \preg_match($this->excludedPattern, \str_replace('\\', '/', $path));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren(): bool
    {
        return $this->isRecursive && $this->iterator->hasChildren();
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren()
    {
        $children = new self($this->iterator->getChildren(), []);
        $children->excludedDirs = $this->excludedDirs;
        $children->excludedPattern = $this->excludedPattern;

        return $children;
    }
}
