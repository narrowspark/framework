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

/**
 * Filters an iterator by a regular expression.
 *
 * Based on the webmozart glob package
 *
 * @see https://github.com/webmozart/glob/blob/master/src/Iterator/RegexFilterIterator.php
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class RegexFilterIterator extends FilterIterator
{
    /**
     * Mode: Filters the values of the inner iterator.
     */
    public const FILTER_VALUE = 1;

    /**
     * Mode: Filters the keys of the inner iterator.
     */
    public const FILTER_KEY = 2;

    /**
     * Mode: Return incrementing numbers as keys.
     */
    public const CURSOR_AS_KEY = 16;

    /**
     * Mode: Return the original keys as keys.
     */
    public const KEY_AS_KEY = 32;

    /** @var string */
    private $regExp;

    /** @var string */
    private $staticPrefix;

    /** @var int */
    private $cursor = 0;

    /** @var null|int */
    private $mode;

    /**
     * Create a new RegexFilterIterator instance.
     *
     * @param string   $regExp        the regular expression to filter by
     * @param string   $staticPrefix  the static prefix of the regular expression
     * @param Iterator $innerIterator the filtered iterator
     * @param int      $mode          a bitwise combination of the mode constants
     */
    public function __construct(string $regExp, string $staticPrefix, Iterator $innerIterator, ?int $mode = null)
    {
        parent::__construct($innerIterator);

        if (($mode & (self::FILTER_KEY | self::FILTER_VALUE)) === 0) {
            $mode |= self::FILTER_VALUE;
        }

        if (($mode & (self::CURSOR_AS_KEY | self::KEY_AS_KEY)) === 0) {
            $mode |= self::CURSOR_AS_KEY;
        }

        $this->regExp = $regExp;
        $this->staticPrefix = $staticPrefix;
        $this->mode = $mode;
    }

    /**
     * Rewind the iterator to the first position.
     *
     * @return void
     */
    public function rewind(): void
    {
        parent::rewind();

        $this->cursor = 0;
    }

    /**
     * Returns the current position.
     *
     * @return mixed the current position
     */
    public function key()
    {
        if (! $this->valid()) {
            return null;
        }

        if (($this->mode & self::KEY_AS_KEY) !== 0) {
            return parent::key();
        }

        return $this->cursor;
    }

    /**
     * Advances to the next match.
     *
     * @see Iterator::next()
     *
     * @return void
     */
    public function next(): void
    {
        if ($this->valid()) {
            parent::next();

            $this->cursor++;
        }
    }

    /**
     * Accepts paths matching the glob.
     *
     * @return bool whether the path is accepted
     */
    public function accept(): bool
    {
        $path = ($this->mode & self::FILTER_VALUE) !== 0 ? $this->current() : parent::key();

        if (\strpos($path, $this->staticPrefix) !== 0) {
            return false;
        }

        return (bool) \preg_match($this->regExp, $path);
    }
}
