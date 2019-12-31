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

namespace Viserio\Component\Finder;

use AppendIterator;
use ArrayIterator;
use Closure;
use Iterator;
use IteratorAggregate;
use RecursiveIteratorIterator;
use SplFileInfo;
use Traversable;
use Viserio\Component\Finder\Comparator\DateComparator;
use Viserio\Component\Finder\Comparator\NumberComparator;
use Viserio\Component\Finder\Filter\CustomFilterIterator;
use Viserio\Component\Finder\Filter\DateRangeFilterIterator;
use Viserio\Component\Finder\Filter\DepthRangeFilterIterator;
use Viserio\Component\Finder\Filter\ExcludeDirectoryFilterIterator;
use Viserio\Component\Finder\Filter\FileContentFilterIterator;
use Viserio\Component\Finder\Filter\FilenameFilterIterator;
use Viserio\Component\Finder\Filter\FileTypeFilterIterator;
use Viserio\Component\Finder\Filter\PathFilterIterator;
use Viserio\Component\Finder\Filter\SizeRangeFilterIterator;
use Viserio\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Viserio\Component\Finder\Iterator\SortableIterator;
use Viserio\Contract\Finder\Comparator\DateComparator as DateComparatorContract;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;
use Viserio\Contract\Finder\Exception\LogicException;
use Viserio\Contract\Finder\Exception\NotFoundException;
use Viserio\Contract\Finder\Exception\RuntimeException;
use Viserio\Contract\Finder\Finder as FinderContract;

/**
 * Finder allows to build rules to find files and directories.
 *
 * It is a thin wrapper around several specialized iterator classes.
 *
 * All rules may be invoked several times.
 *
 * All methods return the current Finder object to allow chaining:
 *
 *     $finder = Finder::create()->files()->name('*.php')->in(__DIR__);
 *
 * Based on the symfony finder package
 *
 * @see https://github.com/symfony/symfony/blob/5.0/src/Symfony/Component/Finder/Finder.php
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Finder implements FinderContract
{
    /** @var int */
    public const IGNORE_VCS_FILES = 1;

    /** @var int */
    public const IGNORE_DOT_FILES = 2;

    /** @var int */
    public const IGNORE_VCS_IGNORED_FILES = 4;

    /** @var int */
    private $mode = 0;

    /** @var string[] */
    private $names = [];

    /** @var string[] */
    private $notNames = [];

    /** @var string[] */
    private $exclude = [];

    /** @var callable[] */
    private $filters = [];

    /** @var \Viserio\Contract\Finder\Comparator\Comparator[] */
    private $depths = [];

    /** @var \Viserio\Component\Finder\Comparator\NumberComparator[] */
    private $sizes = [];

    /** @var bool */
    private $followLinks = false;

    /** @var bool */
    private $reverseSorting = false;

    /** @var callable|Closure|int */
    private $sort;

    /** @var int */
    private $ignore;

    /** @var string[] */
    private $dirs = [];

    /** @var \Viserio\Component\Finder\Comparator\DateComparator[] */
    private $dates = [];

    /** @var iterable[] */
    private $iterators = [];

    /** @var string[] */
    private $contains = [];

    /** @var string[] */
    private $notContains = [];

    /** @var string[] */
    private $paths = [];

    /** @var string[] */
    private $notPaths = [];

    /** @var bool */
    private $ignoreUnreadableDirs = false;

    /**
     * List of all vcs patterns.
     *
     * @var string[]
     */
    private static $vcsPatterns = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];

    /**
     * Create a new Finder instance.
     */
    public function __construct()
    {
        $this->ignore = static::IGNORE_VCS_FILES | static::IGNORE_DOT_FILES;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Viserio\Component\Finder\Filter\ExcludeDirectoryFilterIterator
     */
    public function exclude($dirs): FinderContract
    {
        $this->exclude = \array_merge($this->exclude, (array) $dirs);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function followLinks(): FinderContract
    {
        $this->followLinks = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseSorting(): FinderContract
    {
        $this->reverseSorting = true;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see SortableIterator
     */
    public function sort(Closure $closure): FinderContract
    {
        $this->sort = $closure;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see FilecontentFilterIterator
     */
    public function contains($patterns): FinderContract
    {
        $this->contains = \array_merge($this->contains, (array) $patterns);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see FilecontentFilterIterator
     */
    public function notContains($patterns): FinderContract
    {
        $this->notContains = \array_merge($this->notContains, (array) $patterns);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreUnreadableDirs(bool $ignore = true): FinderContract
    {
        $this->ignoreUnreadableDirs = $ignore;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(): FinderContract
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function directories(): FinderContract
    {
        $this->mode = FileTypeFilterIterator::ONLY_DIRECTORIES;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function files(): FinderContract
    {
        $this->mode = FileTypeFilterIterator::ONLY_FILES;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see DepthRangeFilterIterator
     * @see NumberComparator
     */
    public function depth($levels): FinderContract
    {
        foreach ((array) $levels as $level) {
            $this->depths[] = new NumberComparator($level);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see strtotime
     * @see DateRangeFilterIterator
     * @see DateComparator
     */
    public function date($dates, string $timeType = DateComparatorContract::LAST_MODIFIED): FinderContract
    {
        foreach ((array) $dates as $date) {
            $this->dates[] = new DateComparator($date, $timeType);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see FilenameFilterIterator
     */
    public function name($patterns): FinderContract
    {
        $this->names = \array_merge($this->names, (array) $patterns);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see FilenameFilterIterator
     */
    public function notName($patterns): FinderContract
    {
        $this->notNames = \array_merge($this->notNames, (array) $patterns);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see FilenameFilterIterator
     */
    public function path($patterns): FinderContract
    {
        $this->paths = \array_merge($this->paths, (array) $patterns);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see FilenameFilterIterator
     */
    public function notPath($patterns): FinderContract
    {
        $this->notPaths = \array_merge($this->notPaths, (array) $patterns);

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see SizeRangeFilterIterator
     * @see NumberComparator
     */
    public function size($sizes): FinderContract
    {
        foreach ((array) $sizes as $size) {
            $this->sizes[] = new NumberComparator($size);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     */
    public function ignoreDotFiles(bool $ignoreDotFiles): FinderContract
    {
        if ($ignoreDotFiles) {
            $this->ignore |= static::IGNORE_DOT_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_DOT_FILES;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Viserio\Component\Finder\Filter\ExcludeDirectoryFilterIterator
     */
    public function ignoreVCS(bool $ignoreVCS): FinderContract
    {
        if ($ignoreVCS) {
            $this->ignore |= static::IGNORE_VCS_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_VCS_FILES;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function ignoreVCSIgnored(bool $ignoreVCSIgnored): FinderContract
    {
        if ($ignoreVCSIgnored) {
            $this->ignore |= static::IGNORE_VCS_IGNORED_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_VCS_IGNORED_FILES;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see ignoreVCS()
     */
    public static function addVCSPattern($pattern): void
    {
        foreach ((array) $pattern as $p) {
            self::$vcsPatterns[] = $p;
        }

        self::$vcsPatterns = \array_unique(self::$vcsPatterns);
    }

    /**
     * {@inheritdoc}
     *
     * @see SortableIterator
     */
    public function sortByName(bool $useNaturalSort = false): FinderContract
    {
        $this->sort = $useNaturalSort ? SortableIterator::SORT_BY_NAME_NATURAL : SortableIterator::SORT_BY_NAME;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see SortableIterator
     */
    public function sortByType(): FinderContract
    {
        $this->sort = SortableIterator::SORT_BY_TYPE;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see SortableIterator
     */
    public function sortByAccessedTime(): FinderContract
    {
        $this->sort = SortableIterator::SORT_BY_ACCESSED_TIME;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see SortableIterator
     */
    public function sortByChangedTime(): FinderContract
    {
        $this->sort = SortableIterator::SORT_BY_CHANGED_TIME;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see SortableIterator
     */
    public function sortByModifiedTime(): FinderContract
    {
        $this->sort = SortableIterator::SORT_BY_MODIFIED_TIME;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @see CustomFilterIterator
     */
    public function filter(callable $closure): FinderContract
    {
        $this->filters[] = $closure;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function in($dirs): FinderContract
    {
        $resolvedDirs = [];

        foreach ((array) $dirs as $dir) {
            if (\is_dir($dir)) {
                $resolvedDirs[] = $this->normalizeDir($dir);
            } elseif (\count($glob = glob($dir, (\defined('GLOB_BRACE') ? \GLOB_BRACE : 0) | \GLOB_ONLYDIR | \GLOB_NOSORT)) !== 0) {
                \sort($glob);

                $resolvedDirs = \array_merge($resolvedDirs, \array_map([$this, 'normalizeDir'], $glob));
            } else {
                throw new NotFoundException(NotFoundException::TYPE_DIR, \sprintf('The [%s] directory does not exist.', $dir));
            }
        }

        $this->dirs = \array_merge($this->dirs, $resolvedDirs);

        return $this;
    }

    /**
     * Returns an Iterator for the current Finder configuration.
     *
     * This method implements the IteratorAggregate interface.
     *
     * @throws \Viserio\Contract\Finder\Exception\LogicException if the in() method has not been called
     *
     * @return \Traversable<int|string, \Viserio\Contract\Finder\SplFileInfo> An iterator
     */
    public function getIterator(): Traversable
    {
        if (\count($this->dirs) === 0 && \count($this->iterators) === 0) {
            throw new LogicException('You must call one of in() or append() methods before iterating over a Finder.');
        }

        if (\count($this->dirs) === 1 && \count($this->iterators) === 0) {
            return $this->searchInDirectory($this->dirs[0]);
        }

        $iterator = new AppendIterator();

        foreach ($this->dirs as $dir) {
            $iterator->append($this->searchInDirectory($dir));
        }

        foreach ($this->iterators as $it) {
            $iterator->append($it);
        }

        return $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function append(iterable $iterator): FinderContract
    {
        if ($iterator instanceof IteratorAggregate) {
            $this->iterators[] = $iterator->getIterator();
        } elseif ($iterator instanceof Iterator) {
            $this->iterators[] = $iterator;
        } elseif ($iterator instanceof Traversable || \is_array($iterator)) {
            $it = new ArrayIterator();

            foreach ($iterator as $file) {
                $it->append($file instanceof SplFileInfo ? $file : new SplFileInfo($file));
            }

            $this->iterators[] = $it;
        } else {
            throw new InvalidArgumentException('Finder::append() method wrong argument type.');
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasResults(): bool
    {
        foreach ($this->getIterator() as $_) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        return \iterator_count($this->getIterator());
    }

    /**
     * Search in given dir path.
     *
     * @param string $dir
     *
     * @return Traversable<int|string, SplFileInfo>
     */
    private function searchInDirectory(string $dir): Traversable
    {
        $exclude = $this->exclude;
        $notPaths = $this->notPaths;

        if (static::IGNORE_VCS_FILES === (static::IGNORE_VCS_FILES & $this->ignore)) {
            $exclude = \array_merge($exclude, self::$vcsPatterns);
        }

        if (static::IGNORE_DOT_FILES === (static::IGNORE_DOT_FILES & $this->ignore)) {
            $notPaths[] = '#(^|/)\..+(/|$)#';
        }

        if (static::IGNORE_VCS_IGNORED_FILES === (static::IGNORE_VCS_IGNORED_FILES & $this->ignore)) {
            $gitignoreFilePath = \sprintf('%s/.gitignore', $dir);

            if (! \is_readable($gitignoreFilePath)) {
                throw new RuntimeException(\sprintf('The "ignoreVCSIgnored" option cannot be used by the Finder as the [%s] file is not readable.', $gitignoreFilePath));
            }

            $notPaths[] = Gitignore::toRegex((string) \file_get_contents($gitignoreFilePath));
        }

        $minDepth = 0;
        $maxDepth = \PHP_INT_MAX;

        foreach ($this->depths as $comparator) {
            switch ($comparator->getOperator()) {
                case '>':
                    $minDepth = $comparator->getTarget() + 1;

                    break;
                case '>=':
                    $minDepth = $comparator->getTarget();

                    break;
                case '<':
                    $maxDepth = $comparator->getTarget() - 1;

                    break;
                case '<=':
                    $maxDepth = $comparator->getTarget();

                    break;

                default:
                    $minDepth = $maxDepth = $comparator->getTarget();
            }
        }

        $flags = RecursiveDirectoryIterator::SKIP_DOTS;

        if ($this->followLinks) {
            $flags |= RecursiveDirectoryIterator::FOLLOW_SYMLINKS;
        }

        $iterator = new RecursiveDirectoryIterator($dir, $flags, $this->ignoreUnreadableDirs);

        if (\count($exclude) !== 0) {
            $iterator = new ExcludeDirectoryFilterIterator($iterator, $exclude);
        }

        $iterator = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);

        if ($minDepth > 0 || $maxDepth < \PHP_INT_MAX) {
            $iterator = new DepthRangeFilterIterator($iterator, (int) $minDepth, (int) $maxDepth);
        }

        if ($this->mode !== 0) {
            $iterator = new FileTypeFilterIterator($iterator, $this->mode);
        }

        if (\count($this->names) !== 0 || \count($this->notNames) !== 0) {
            $iterator = new FilenameFilterIterator($iterator, $this->names, $this->notNames);
        }

        if (\count($this->contains) !== 0 || \count($this->notContains) !== 0) {
            $iterator = new FilecontentFilterIterator($iterator, $this->contains, $this->notContains);
        }

        if (\count($this->sizes) !== 0) {
            $iterator = new SizeRangeFilterIterator($iterator, $this->sizes);
        }

        if (\count($this->dates) !== 0) {
            $iterator = new DateRangeFilterIterator($iterator, $this->dates);
        }

        if (\count($this->filters) !== 0) {
            $iterator = new CustomFilterIterator($iterator, ...$this->filters);
        }

        if (\count($this->paths) !== 0 || \count($notPaths) !== 0) {
            $iterator = new PathFilterIterator($iterator, $this->paths, $notPaths);
        }

        if ($this->sort !== null || $this->reverseSorting) {
            $iteratorAggregate = new SortableIterator($iterator, $this->sort, $this->reverseSorting);
            $iterator = $iteratorAggregate->getIterator();
        }

        return $iterator;
    }

    /**
     * Normalizes given directory names by removing trailing slashes.
     *
     * Excluding: (s)ftp:// or ssh2.(s)ftp:// wrapper
     *
     * @param string $dir
     *
     * @return string
     */
    private function normalizeDir(string $dir): string
    {
        $dir = \rtrim($dir, '/' . \DIRECTORY_SEPARATOR);

        if (\preg_match('#^(ssh2\.)?s?ftp://#', $dir) !== false) {
            $dir .= '/';
        }

        return $dir;
    }
}
