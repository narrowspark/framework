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
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws Throwable
     */
    public function readlink(string $path, bool $canonicalize = false): ?string;

    /**
     * Creates a hard link, or several hard links to a file.
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException       When link fails, including if link already exists
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException When original file is missing or not a file
     * @throws Throwable
     */
    public function hardlink(string $originFile, string $targetFile): void;

    /**
     * Creates a symbolic link to the target file or directory.
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws Throwable
     */
    public function symlink(string $origin, string $target): void;

    /**
     * Tells whether the filename is a symbolic link.
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws Throwable
     */
    public function isLink(string $filename): bool;
}
