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

use Iterator;
use SplFileInfo;

interface DirectorySystem
{
    /**
     * Get all of the directories within a given directory.
     *
     * @return Iterator<string, SplFileInfo>
     */
    public function directories(string $directory): Iterator;

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @return Iterator<string, SplFileInfo>
     */
    public function allDirectories(string $directory): Iterator;

    /**
     * Creates a directory recursively.
     *
     * @param array<string, string> $config An array of boolean options
     *                                      Valid options are:
     *                                      - $config['visibility'] Whether to change the file chmod (defaults to public visibility)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     */
    public function createDirectory(string $dirname, array $config = []): void;

    /**
     * Recursively delete a directory.
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\UnreadableFileException
     * @throws \Viserio\Contract\Finder\Exception\NotFoundException
     */
    public function deleteDirectory(string $dirname): void;

    /**
     * Empty the specified directory of all files and folders.
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\UnreadableFileException
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     */
    public function cleanDirectory(string $dirname): void;

    /**
     * Determine if the given path is a directory.
     */
    public function isDirectory(string $dirname): bool;

    /**
     * Copy a directory from one location to another.
     *
     * @param array<string, int> $config An array of boolean options
     *                                   Valid options are:
     *                                   - $config['flags'] @see https://php.net/manual/en/filesystemiterator.construct.php and than the flags section
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     */
    public function copyDirectory(string $directory, string $destination, array $config = []): void;
}
