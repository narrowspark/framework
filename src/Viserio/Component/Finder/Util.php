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

use Viserio\Component\Filesystem\Path;
use Viserio\Contract\Finder\Exception\InvalidArgumentException;

/**
 * Based on the webmozart glob package.
 *
 * @see https://github.com/webmozart/glob/blob/master/src/Glob.php
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
final class Util
{
    /**
     * Returns the base path of a glob.
     *
     * This method returns the most specific directory that contains all files
     * matched by the glob. If this directory does not exist on the file system,
     * it's not necessary to execute the glob algorithm.
     *
     * More specifically, the "base path" is the longest path trailed by a "/"
     * on the left of the first wildcard "*". If the glob does not contain
     * wildcards, the directory name of the glob is returned.
     *
     * ```php
     * Util::getBasePath('/css/*.css');
     * // => /css
     *
     * Util::getBasePath('/css/style.css');
     * // => /css
     *
     * Util::getBasePath('/css/st*.css');
     * // => /css
     *
     * Util::getBasePath('/*.css');
     * // => /
     * ```
     *
     * @param string $glob The canonical glob. The glob should contain forward
     *                     slashes as directory separators only. It must not
     *                     contain any "." or ".." segments. Use the
     *                     "Path::canonicalize" to canonicalize globs
     *                     prior to calling this method.
     *
     * @return string the base path of the glob
     */
    public static function getBasePath(string $glob): string
    {
        // Search the static prefix for the last "/"
        $staticPrefix = self::getStaticPrefix($glob);

        if (($pos = \strrpos($staticPrefix, '/')) !== false) {
            // Special case: Return "/" if the only slash is at the beginning
            // of the glob
            if ($pos === 0) {
                return '/';
            }

            // Special case: Include trailing slash of "scheme:///foo"
            if ($pos - 3 === \strpos($glob, '://')) {
                return \substr($staticPrefix, 0, $pos + 1);
            }

            return \substr($staticPrefix, 0, $pos);
        }
        // Finder contains no slashes on the left of the wildcard
        // Return an empty string
        return '';
    }

    /**
     * Returns the static prefix of a glob.
     *
     * The "static prefix" is the part of the glob up to the first wildcard "*".
     * If the glob does not contain wildcards, the full glob is returned.
     *
     * @param string $glob The canonical glob. The glob should contain forward
     *                     slashes as directory separators only. It must not
     *                     contain any "." or ".." segments. Use the
     *                     "Path::canonicalize" to canonicalize globs
     *                     prior to calling this method.
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException
     *
     * @return string the static prefix of the glob
     */
    public static function getStaticPrefix(string $glob): string
    {
        if (! Path::isAbsolute($glob) && \strpos($glob, '://') === false) {
            throw new InvalidArgumentException(\sprintf('The glob [%s] is not absolute and not a URI.', $glob));
        }

        $prefix = '';
        $length = \strlen($glob);

        for ($i = 0; $i < $length; $i++) {
            $c = $glob[$i];

            switch ($c) {
                case '/':
                    $prefix .= '/';

                    if (isset($glob[$i + 3]) && '**/' === $glob[$i + 1] . $glob[$i + 2] . $glob[$i + 3]) {
                        break 2;
                    }

                    break;
                case '*':
                case '?':
                case '{':
                case '[':
                    break 2;
                case '\\':
                    if (isset($glob[$i + 1])) {
                        switch ($glob[$i + 1]) {
                            case '*':
                            case '?':
                            case '{':
                            case '[':
                            case '\\':
                                $prefix .= $glob[$i + 1];
                                $i++;

                                break;

                            default:
                                $prefix .= '\\';
                        }
                    } else {
                        $prefix .= '\\';
                    }

                    break;

                default:
                    $prefix .= $c;

                    break;
            }
        }

        return $prefix;
    }

    /**
     * Converts a glob to a regular expression.
     *
     * Use this method if you need to match many paths against a glob:
     *
     * ```php
     * $staticPrefix = Util::getStaticPrefix('/project/**.twig');
     * $regEx = Util::toRegEx('/project/**.twig');
     *
     * if (0 !== strpos($path, $staticPrefix)) {
     *     // no match
     * }
     *
     * if (!preg_match($regEx, $path)) {
     *     // no match
     * }
     * ```
     *
     * You should always test whether a path contains the static prefix of the
     * glob returned by {@link getStaticPrefix()} to reduce the number of calls
     * to the expensive {@link preg_match()}.
     *
     * @param string $glob                      The canonical glob. The glob should contain forward
     *                                          slashes as directory separators only. It must not
     *                                          contain any "." or ".." segments. Use the
     *                                          "Path::canonicalize" to canonicalize globs
     *                                          prior to calling this method.
     * @param string $delimiter
     * @param bool   $checkForAbsolutePathOrUri
     *
     * @throws \Viserio\Contract\Finder\Exception\InvalidArgumentException
     *
     * @return string the regular expression for matching the glob
     */
    public static function toRegEx(
        string $glob,
        string $delimiter = '~',
        bool $checkForAbsolutePathOrUri = true
    ): string {
        if ($checkForAbsolutePathOrUri && ! Path::isAbsolute($glob) && \strpos($glob, '://') === false) {
            throw new InvalidArgumentException(\sprintf('The glob [%s] is not absolute and not a URI.', $glob));
        }

        $inSquare = false;
        $curlyLevels = 0;
        $regex = '';
        $length = \strlen($glob);

        for ($i = 0; $i < $length; $i++) {
            $c = $glob[$i];

            switch ($c) {
                case '.':
                case '(':
                case ')':
                case '|':
                case '+':
                case '^':
                case '$':
                case $delimiter:
                    $regex .= "\\{$c}";

                    break;
                case '/':
                    if (isset($glob[$i + 3]) && '**/' === $glob[$i + 1] . $glob[$i + 2] . $glob[$i + 3]) {
                        $regex .= '/([^/]+/)*';
                        $i += 3;
                    } else {
                        $regex .= '/';
                    }

                    break;
                case '*':
                    $regex .= '[^/]*';

                    break;
                case '?':
                    $regex .= '.';

                    break;
                case '{':
                    $regex .= '(';
                    $curlyLevels++;

                    break;
                case '}':
                    if ($curlyLevels > 0) {
                        $regex .= ')';
                        $curlyLevels--;
                    } else {
                        $regex .= '}';
                    }

                    break;
                case ',':
                    $regex .= $curlyLevels > 0 ? '|' : ',';

                    break;
                case '[':
                    $regex .= '[';
                    $inSquare = true;

                    if (isset($glob[$i + 1]) && '^' === $glob[$i + 1]) {
                        $regex .= '^';
                        $i++;
                    }

                    break;
                case ']':
                    $regex .= $inSquare ? ']' : '\\]';
                    $inSquare = false;

                    break;
                case '-':
                    $regex .= $inSquare ? '-' : '\\-';

                    break;
                case '\\':
                    if (isset($glob[$i + 1])) {
                        switch ($glob[$i + 1]) {
                            case '*':
                            case '?':
                            case '{':
                            case '}':
                            case '[':
                            case ']':
                            case '-':
                            case '^':
                            case '\\':
                                $regex .= '\\' . $glob[$i + 1];
                                $i++;

                                break;

                            default:
                                $regex .= '\\\\';
                        }
                    } else {
                        $regex .= '\\\\';
                    }

                    break;

                default:
                    $regex .= $c;

                    break;
            }
        }

        if ($inSquare) {
            throw new InvalidArgumentException(\sprintf('Invalid glob: missing ] in %s', $glob));
        }

        if ($curlyLevels > 0) {
            throw new InvalidArgumentException(\sprintf('Invalid glob: missing } in %s', $glob));
        }

        return $delimiter . '^' . $regex . '$' . $delimiter;
    }

    /**
     * @internal
     *
     * Wrapper for glob with fallback if GLOB_BRACE is not available.
     *
     * Code is largely lifted from the Zend\Stdlib\Glob implementation in
     * Zend Framework, released with the copyright and license below.
     *
     * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
     * @license   http://framework.zend.com/license/new-bsd New BSD License
     *
     * @see https://github.com/zendframework/zend-stdlib/issues/58
     *
     * @param string $pattern
     * @param int    $flags
     *
     * @return array|false
     */
    public static function polyfillGlobBrace(string $pattern, int $flags = 0)
    {
        static $nextBraceSub;

        if (! $nextBraceSub) {
            // Find the end of the sub-pattern in a brace expression.
            $nextBraceSub = static function (string $pattern, int $current) {
                $length = \strlen($pattern);
                $depth = 0;

                while ($current < $length) {
                    if ($pattern[$current] === '\\') {
                        if (++$current === $length) {
                            break;
                        }

                        $current++;
                    } else {
                        if (($pattern[$current] === '}' && $depth-- === 0) || ($pattern[$current] === ',' && $depth === 0)) {
                            break;
                        }

                        if ($pattern[$current++] === '{') {
                            $depth++;
                        }
                    }
                }

                return $current < $length ? $current : null;
            };
        }

        $length = \strlen($pattern);
        // Find first opening brace.
        for ($begin = 0; $begin < $length; $begin++) {
            if ($pattern[$begin] === '\\') {
                $begin++;
            } elseif ($pattern[$begin] === '{') {
                break;
            }
        }

        // Find comma or matching closing brace.
        if (($next = $nextBraceSub($pattern, $begin + 1)) === null) {
            return \glob($pattern, $flags);
        }

        $rest = $next;

        // Point `$rest` to matching closing brace.
        while ($pattern[$rest] !== '}') {
            if (null === ($rest = $nextBraceSub($pattern, $rest + 1))) {
                return \glob($pattern, $flags);
            }
        }

        $paths = [];
        $p = $begin + 1;

        // For each comma-separated sub-pattern.
        do {
            $subPattern = \substr($pattern, 0, $begin)
                . \substr($pattern, $p, $next - $p)
                . \substr($pattern, $rest + 1);

            if (false !== $results = self::polyfillGlobBrace($subPattern, $flags)) {
                foreach ($results as $result) {
                    $paths[] = $result;
                }
            }

            if ($pattern[$next] === '}') {
                break;
            }

            $p = $next + 1;
            $next = $nextBraceSub($pattern, $p);
        } while (null !== $next);

        return \array_unique($paths);
    }
}
