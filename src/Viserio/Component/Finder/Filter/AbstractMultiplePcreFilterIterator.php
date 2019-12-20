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
 * AbstractMultiplePcreFilterIterator filters files using patterns (regexps, globs or strings).
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class AbstractMultiplePcreFilterIterator extends FilterIterator
{
    /** @var array */
    protected $matchRegexps = [];

    /** @var array */
    protected $noMatchRegexps = [];

    /**
     * @param Iterator $iterator        The Iterator to filter
     * @param string[] $matchPatterns   An array of patterns that need to match
     * @param string[] $noMatchPatterns An array of patterns that need to not match
     */
    public function __construct(Iterator $iterator, array $matchPatterns, array $noMatchPatterns)
    {
        foreach ($matchPatterns as $pattern) {
            $this->matchRegexps[] = $this->toRegex($pattern);
        }

        foreach ($noMatchPatterns as $pattern) {
            $this->noMatchRegexps[] = $this->toRegex($pattern);
        }

        parent::__construct($iterator);
    }

    /**
     * Checks whether the string is accepted by the regex filters.
     *
     * If there is no regexps defined in the class, this method will accept the string.
     * Such case can be handled by child classes before calling the method if they want to
     * apply a different behavior.
     *
     * @param string $string
     *
     * @return bool
     */
    protected function isAccepted(string $string): bool
    {
        // should at least not match one rule to exclude
        foreach ($this->noMatchRegexps as $regex) {
            if ((bool) \preg_match($regex, $string)) {
                return false;
            }
        }

        // should at least match one rule
        if (\count($this->matchRegexps) !== 0) {
            foreach ($this->matchRegexps as $regex) {
                if ((bool) \preg_match($regex, $string)) {
                    return true;
                }
            }

            return false;
        }

        // If there is no match rules, the file is accepted
        return true;
    }

    /**
     * Checks whether the string is a regex.
     *
     * @param string $string
     *
     * @return bool
     */
    protected function isRegex(string $string): bool
    {
        if (\preg_match('/^(.{3,}?)[imsxuADU]*$/', $string, $m)) {
            $start = \substr($m[1], 0, 1);
            $end = \substr($m[1], -1);

            if ($start === $end) {
                return ! \preg_match('/[*?[:alnum:] \\\\]/', $start);
            }

            foreach ([['{', '}'], ['(', ')'], ['[', ']'], ['<', '>']] as $delimiters) {
                if ($start === $delimiters[0] && $end === $delimiters[1]) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Converts string into regexp.
     *
     * @param string $string
     *
     * @return string
     */
    abstract protected function toRegex(string $string): string;
}
