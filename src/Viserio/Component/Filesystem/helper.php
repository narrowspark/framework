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

namespace Viserio\Component\Filesystem;

use Viserio\Component\Filesystem\Iterator\GlobIterator;

/**
 * Globs the file system paths matching the glob.
 *
 * The glob may contain the wildcard "*". This wildcard matches any number
 * of characters, *including* directory separators.
 *
 * ```php
 * foreach (Glob::glob('/project/**.twig') as $path) {
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
