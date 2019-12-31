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
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Iterator/ExcludeDirectoryFilterIterator.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ExcludeDirectoryFilterIterator extends FilterIterator implements RecursiveIterator
{
    /**
     * The Iterator to filter.
     *
     * @var Iterator<int|string, \Viserio\Contract\Finder\SplFileInfo>|RecursiveIterator<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    private $iterator;

    /** @var bool */
    private $isRecursive;

    /** @var array<string, bool> */
    private $excludedDirs = [];

    /**
     * Exclude regex with all given exclude directories.
     *
     * @var null|string
     */
    private $excludedPattern;

    /**
     * Create a new ExcludeDirectoryFilterIterator instance.
     *
     * @param Iterator<int|string, \Viserio\Contract\Finder\SplFileInfo>|RecursiveIterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator
     * @param string[]                                                                                                                       $directories An array of directories to exclude
     */
    public function __construct(Iterator $iterator, array $directories)
    {
        $this->iterator = $iterator;
        $this->isRecursive = $iterator instanceof RecursiveIterator;

        $patterns = [];

        foreach ($directories as $directory) {
            $directory = \rtrim($directory, '/');

            if (! $this->isRecursive || \strpos($directory, '/') !== false) {
                $patterns[] = \preg_quote($directory, '#');
            } else {
                $this->excludedDirs[$directory] = true;
            }
        }

        if (\count($patterns) !== 0) {
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

        if ($this->excludedPattern !== null) {
            $path = $this->isDir() ? $this->current()->getSubPathname() : $this->current()->getRelativePath();

            return \preg_match($this->excludedPattern, \str_replace('\\', '/', $path)) !== 1;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChildren(): bool
    {
        if ($this->isRecursive) {
            /** @var \RecursiveIterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator */
            $iterator = $this->iterator;

            return $iterator->hasChildren();
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @return \RecursiveIterator<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function getChildren(): RecursiveIterator
    {
        /** @var \RecursiveIterator<int|string, \Viserio\Contract\Finder\SplFileInfo> $iterator */
        $iterator = $this->iterator;

        $children = new self($iterator->getChildren(), []);
        $children->excludedDirs = $this->excludedDirs;
        $children->excludedPattern = $this->excludedPattern;

        return $children;
    }
}
