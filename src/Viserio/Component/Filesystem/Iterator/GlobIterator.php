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

use ArrayIterator;
use EmptyIterator;
use IteratorIterator;
use RecursiveIteratorIterator;
use Viserio\Component\Filesystem\Path;
use Viserio\Contract\Filesystem\Exception\InvalidArgumentException;

/**
 * Returns filesystem paths matching a glob.
 *
 * Based on the webmozart glob package
 *
 * @see https://github.com/webmozart/glob/blob/master/src/Glob.php
 * @see https://github.com/webmozart/glob/blob/master/src/Iterator/GlobIterator.php
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class GlobIterator extends IteratorIterator
{
    /**
     * Create a new GlobIterator instance.
     *
     * @param string $glob  the glob pattern
     * @param int    $flags a bitwise combination of the flag constants
     */
    public function __construct(string $glob, int $flags = 0)
    {
        if (file_exists($glob) && ! (\strpos($glob, '*') !== false || \strpos($glob, '{') !== false || \strpos($glob, '?') !== false || \strpos($glob, '[') !== false)) {
            // If the glob is a file path, return that path
            $innerIterator = new ArrayIterator([$glob]);
        } elseif (\is_dir($basePath = self::getBasePath($glob, $flags))) {
            // Use the system's much more efficient glob() function where we can
            if (
                // glob() does not support /**/
                false === \strpos($glob, '/**/')
                // glob() does not support stream wrappers
                && false === \strpos($glob, '://')
                // glob() does not support [^...] on Windows
                && ('\\' !== \DIRECTORY_SEPARATOR || false === \strpos($glob, '[^'))
            ) {
                $results = \glob($glob, \GLOB_BRACE);

                // $results may be empty or false if $glob is invalid
                if ($results === false || \count($results) === 0) {
                    // Parse glob and provoke errors if invalid
                    self::toRegEx($glob);

                    // Otherwise return empty result set
                    $innerIterator = new EmptyIterator();
                } else {
                    $innerIterator = new ArrayIterator($results);
                }
            } else {
                // Otherwise scan the glob's base directory for matches
                $innerIterator = new GlobFilterIterator(
                    $glob,
                    new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator(
                            $basePath,
                            RecursiveDirectoryIterator::CURRENT_AS_PATHNAME
                            | RecursiveDirectoryIterator::SKIP_DOTS
                        ),
                        RecursiveIteratorIterator::SELF_FIRST
                    ),
                    GlobFilterIterator::FILTER_VALUE,
                    $flags
                );
            }
        } else {
            // If the glob's base directory does not exist, return nothing
            $innerIterator = new EmptyIterator();
        }

        parent::__construct($innerIterator);
    }

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
     * GlobIterator::getBasePath('/css/*.css');
     * // => /css
     *
     * GlobIterator::getBasePath('/css/style.css');
     * // => /css
     *
     * GlobIterator::getBasePath('/css/st*.css');
     * // => /css
     *
     * GlobIterator::getBasePath('/*.css');
     * // => /
     * ```
     *
     * @param string $glob  The canonical glob. The glob should contain forward
     *                      slashes as directory separators only. It must not
     *                      contain any "." or ".." segments. Use the
     *                      "Path::canonicalize" to canonicalize globs
     *                      prior to calling this method.
     * @param int    $flags a bitwise combination of the flag constants in this
     *                      class
     *
     * @return string the base path of the glob
     */
    public static function getBasePath($glob, $flags = 0)
    {
        // Search the static prefix for the last "/"
        $staticPrefix = self::getStaticPrefix($glob, $flags);

        if (false !== ($pos = \strrpos($staticPrefix, '/'))) {
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
        // Glob contains no slashes on the left of the wildcard
        // Return an empty string
        return '';
    }

    /**
     * Returns the static prefix of a glob.
     *
     * The "static prefix" is the part of the glob up to the first wildcard "*".
     * If the glob does not contain wildcards, the full glob is returned.
     *
     * @param string $glob  The canonical glob. The glob should contain forward
     *                      slashes as directory separators only. It must not
     *                      contain any "." or ".." segments. Use the
     *                      "Path::canonicalize" to canonicalize globs
     *                      prior to calling this method.
     * @param int    $flags a bitwise combination of the flag constants
     *
     * @return string the static prefix of the glob
     */
    public static function getStaticPrefix($glob, int $flags = 0): string
    {
        if (! Path::isAbsolute($glob) && \strpos($glob, '://') === false) {
            throw new InvalidArgumentException(\sprintf('The glob "%s" is not absolute and not a URI.', $glob));
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
     * $staticPrefix = GlobIterator::getStaticPrefix('/project/**.twig');
     * $regEx = GlobIterator::toRegEx('/project/**.twig');
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
     * @param string $glob      The canonical glob. The glob should contain forward
     *                          slashes as directory separators only. It must not
     *                          contain any "." or ".." segments. Use the
     *                          "Path::canonicalize" to canonicalize globs
     *                          prior to calling this method.
     * @param int    $flags     a bitwise combination of the flag constants in this
     *                          class
     * @param string $delimiter
     *
     * @return string the regular expression for matching the glob
     */
    public static function toRegEx(string $glob, int $flags = 0, string $delimiter = '~'): string
    {
        if (! Path::isAbsolute($glob) && \strpos($glob, '://') === false) {
            throw new InvalidArgumentException(\sprintf('The glob "%s" is not absolute and not a URI.', $glob));
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
}
