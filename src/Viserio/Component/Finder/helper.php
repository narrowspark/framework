<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Finder;

use Viserio\Component\Finder\Iterator\GlobIterator;

/**
 * Globs the file system paths matching the glob.
 *
 * The glob may contain the wildcard "*". This wildcard matches any number
 * of characters, *including* directory separators.
 *
 * ```php
 * foreach (Finder::glob('/project/**.twig') as $path) {
 *     // do something...
 * }
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
 * @return string[] The matching paths. The keys of the array are
 *                  incrementing integers.
 */
function glob(string $glob, int $flags = 0)
{
    $results = \iterator_to_array(new GlobIterator($glob, $flags));

    \sort($results);

    return $results;
}
