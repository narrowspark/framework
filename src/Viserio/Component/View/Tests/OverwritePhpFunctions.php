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

namespace Viserio\Component\View;

use Viserio\Component\View\Tests\StaticMemory;

/**
 * Checks whether a file or directory exists.
 *
 * @see https://php.net/manual/en/function.file-exists.php
 *
 * @param string $filename <p>
 *                         Path to the file or directory.
 *                         </p>
 *                         <p>
 *                         On windows, use //computername/share/filename or
 *                         \\computername\share\filename to check files on
 *                         network shares.
 *                         </p>
 *
 * @return bool true if the file or directory specified by
 *              filename exists; false otherwise.
 *              </p>
 *              <p>
 *              This function will return false for symlinks pointing to non-existing
 *              files.
 *              </p>
 *              <p>
 *              This function returns false for files inaccessible due to safe mode restrictions. However these
 *              files still can be included if
 *              they are located in safe_mode_include_dir.
 *              </p>
 *              <p>
 *              The check is done using the real UID/GID instead of the effective one.
 *
 * @since 4.0
 * @since 5.0
 */
function file_exists($filename): bool
{
    $callback = StaticMemory::$fileExists;

    return $callback($filename);
}
