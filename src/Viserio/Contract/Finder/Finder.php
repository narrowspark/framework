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

namespace Viserio\Contract\Finder;

use Closure;
use Countable;
use IteratorAggregate;
use Traversable;
use Viserio\Contract\Finder\Comparator\DateComparator as DateComparatorContract;

interface Finder extends Countable, IteratorAggregate
{
    /**
     * Excludes directories.
     *
     * Directories passed as argument must be relative to the ones defined with the `in()` method. For example:
     *
     *     $finder->in(__DIR__)->exclude('ruby');
     *
     * @param string|string[] $dirs A directory path or an array of directories
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function exclude($dirs): self;

    /**
     * Forces the following of symlinks.
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function followLinks(): self;

    /**
     * Reverses the sorting.
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function reverseSorting(): self;

    /**
     * Sorts files and directories by an anonymous function.
     *
     * The anonymous function receives two \SplFileInfo instances to compare.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @param Closure $closure
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function sort(Closure $closure): self;

    /**
     * Adds tests that file contents must match.
     *
     * Strings or PCRE patterns can be used:
     *
     *     $finder->contains('Lorem ipsum')
     *     $finder->contains('/Lorem ipsum/i')
     *     $finder->contains(['dolor', '/ipsum/i'])
     *
     * @param string|string[] $patterns A pattern (string or regexp) or an array of patterns
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function contains($patterns): self;

    /**
     * Adds tests that file contents must not match.
     *
     * Strings or PCRE patterns can be used:
     *
     *     $finder->notContains('Lorem ipsum')
     *     $finder->notContains('/Lorem ipsum/i')
     *     $finder->notContains(['lorem', '/dolor/i'])
     *
     * @param string|string[] $patterns A pattern (string or regexp) or an array of patterns
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function notContains($patterns): self;

    /**
     * Tells finder to ignore unreadable directories.
     *
     * By default, scanning unreadable directories content throws an AccessDeniedException.
     *
     * @param bool $ignore
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function ignoreUnreadableDirs(bool $ignore = true): self;

    /**
     * Creates a new Finder.
     *
     * @return iterable<string, \Viserio\Contract\Finder\SplFileInfo>&\Viserio\Contract\Finder\Finder
     */
    public static function create(): self;

    /**
     * Restricts the matching to directories only.
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function directories(): self;

    /**
     * Restricts the matching to files only.
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function files(): self;

    /**
     * Adds tests for the directory depth.
     *
     * Usage:
     *
     *     $finder->depth('> 1') // the Finder will start matching at level 1.
     *     $finder->depth('< 3') // the Finder will descend at most 3 levels of directories below the starting point.
     *     $finder->depth(['>= 1', '< 3'])
     *
     * @param int|int[]|string|string[] $levels The depth level expression or an array of depth levels
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function depth($levels): self;

    /**
     * Adds tests for file dates (last modified).
     *
     * The date must be something that strtotime() is able to parse:
     *
     *     $finder->date('since yesterday');
     *     $finder->date('until 2 days ago');
     *     $finder->date('> now - 2 hours');
     *     $finder->date('>= 2005-10-15');
     *     $finder->date(['>= 2005-10-15', '<= 2006-05-27']);
     *
     * @param string|string[] $dates    A date range string or an array of date ranges
     * @param string          $timeType The time type to compare
     *                                  Accessed, Changed or Modified date is the last time the file was:
     *                                  read, written, permissions changed, moved, renamed
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function date($dates, string $timeType = DateComparatorContract::LAST_MODIFIED): self;

    /**
     * Adds rules that files must match.
     *
     * You can use patterns (delimited with / sign), globs or simple strings.
     *
     *     $finder->name('*.php')
     *     $finder->name('/\.php$/') // same as above
     *     $finder->name('test.php')
     *     $finder->name(['test.py', 'test.php'])
     *
     * @param string|string[] $patterns A pattern (a regexp, a glob, or a string) or an array of patterns
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function name($patterns): self;

    /**
     * Adds rules that files must not match.
     *
     * @param string|string[] $patterns A pattern (a regexp, a glob, or a string) or an array of patterns
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function notName($patterns): self;

    /**
     * Adds rules that filenames must match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     *     $finder->path('some/special/dir')
     *     $finder->path('/some\/special\/dir/') // same as above
     *     $finder->path(['some dir', 'another/dir'])
     *
     * Use only / as dirname separator.
     *
     * @param string|string[] $patterns A pattern (a regexp or a string) or an array of patterns
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function path($patterns): self;

    /**
     * Adds rules that filenames must not match.
     *
     * You can use patterns (delimited with / sign) or simple strings.
     *
     *     $finder->notPath('some/special/dir')
     *     $finder->notPath('/some\/special\/dir/') // same as above
     *     $finder->notPath(['some/file.txt', 'another/file.log'])
     *
     * Use only / as dirname separator.
     *
     * @param string|string[] $patterns A pattern (a regexp or a string) or an array of patterns
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function notPath($patterns): self;

    /**
     * Adds tests for file sizes.
     *
     *     $finder->size('> 10K');
     *     $finder->size('<= 1Ki');
     *     $finder->size(4);
     *     $finder->size(['> 10K', '< 20K'])
     *
     * @param int|int[]|string|string[] $sizes A size range string or an integer or an array of size ranges
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function size($sizes): self;

    /**
     * Excludes "hidden" directories and files (starting with a dot).
     *
     * This option is enabled by default.
     *
     * @param bool $ignoreDotFiles
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function ignoreDotFiles(bool $ignoreDotFiles): self;

    /**
     * Forces the finder to ignore version control directories.
     *
     * This option is enabled by default.
     *
     * @param bool $ignoreVCS
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function ignoreVCS(bool $ignoreVCS): self;

    /**
     * Forces Finder to obey .gitignore and ignore files based on rules listed there.
     *
     * This option is disabled by default.
     *
     * @param bool $ignoreVCSIgnored
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function ignoreVCSIgnored(bool $ignoreVCSIgnored): self;

    /**
     * Adds VCS patterns.
     *
     * @param string|string[] $pattern VCS patterns to ignore
     */
    public static function addVCSPattern($pattern): void;

    /**
     * Sorts files and directories by name.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @param bool $useNaturalSort
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function sortByName(bool $useNaturalSort = false): self;

    /**
     * Sorts files and directories by type (directories before files), then by name.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function sortByType(): self;

    /**
     * Sorts files and directories by the last accessed time.
     *
     * This is the time that the file was last accessed, read or written to.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function sortByAccessedTime(): self;

    /**
     * Sorts files and directories by the last inode changed time.
     *
     * This is the time that the inode information was last modified (permissions, owner, group or other metadata).
     *
     * On Windows, since inode is not available, changed time is actually the file creation time.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function sortByChangedTime(): self;

    /**
     * Sorts files and directories by the last modified time.
     *
     * This is the last time the actual contents of the file were last modified.
     *
     * This can be slow as all the matching files and directories must be retrieved for comparison.
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function sortByModifiedTime(): self;

    /**
     * Filters the iterator with an anonymous function.
     *
     * The anonymous function receives a \SplFileInfo and must return false
     * to remove files.
     *
     * @param callable $closure
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function filter(callable $closure): self;

    /**
     * Searches files and directories which match defined rules.
     *
     * @param string|string[] $dirs A directory path or an array of directories
     *
     * @throws \Viserio\Contract\Finder\Exception\NotFoundException if one of the directories does not exist
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function in($dirs): self;

    /**
     * Appends an existing set of files/directories to the finder.
     *
     * The set can be another Finder, an Iterator, an IteratorAggregate, or even a plain array.
     *
     * @param array<int|string, mixed>|Traversable<int|string, mixed> $iterator
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException when the given argument is not iterable
     *
     * @return $this(\Viserio\Contract\Finder\Finder)<int|string, \Viserio\Contract\Finder\SplFileInfo>
     */
    public function append(iterable $iterator): self;

    /**
     * Check if the any results were found.
     *
     * @return bool
     */
    public function hasResults(): bool;
}
