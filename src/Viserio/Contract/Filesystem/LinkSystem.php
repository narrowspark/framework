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

namespace Viserio\Contract\Filesystem;

use Throwable;

interface LinkSystem
{
    /**
     * Resolves links in paths.
     *
     * With $canonicalize = false (default)
     *      - if $path does not exist or is not a link, returns null
     *      - if $path is a link, returns the next direct target of the link without considering the existence of the target
     *
     * With $canonicalize = true
     *      - if $path does not exist, returns null
     *      - if $path exists, returns its absolute fully resolved final version
     *
     * @param string $path
     * @param bool   $canonicalize
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws Throwable
     *
     * @return null|string
     */
    public function readlink(string $path, bool $canonicalize = false): ?string;

    /**
     * Creates a hard link, or several hard links to a file.
     *
     * @param string $originFile
     * @param string $targetFile
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException       When link fails, including if link already exists
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException When original file is missing or not a file
     * @throws Throwable
     *
     * @return void
     */
    public function hardlink(string $originFile, string $targetFile): void;

    /**
     * Creates a symbolic link to the target file or directory.
     *
     * @param string $origin
     * @param string $target
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws Throwable
     *
     * @return void
     */
    public function symlink(string $origin, string $target): void;

    /**
     * Tells whether the filename is a symbolic link.
     *
     * @param string $filename
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws Throwable
     *
     * @return bool
     */
    public function isLink(string $filename): bool;
}
