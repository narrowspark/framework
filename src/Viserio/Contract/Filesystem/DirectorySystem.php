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

use Iterator;
use SplFileInfo;

interface DirectorySystem
{
    /**
     * Get all of the directories within a given directory.
     *
     * @param string $directory
     *
     * @return Iterator<string, SplFileInfo>
     */
    public function directories(string $directory): Iterator;

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param string $directory
     *
     * @return Iterator<string, SplFileInfo>
     */
    public function allDirectories(string $directory): Iterator;

    /**
     * Creates a directory recursively.
     *
     * @param string                $dirname
     * @param array<string, string> $config  An array of boolean options
     *                                       Valid options are:
     *                                       - $config['visibility'] Whether to change the file chmod (defaults to public visibility)
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     *
     * @return void
     */
    public function createDirectory(string $dirname, array $config = []): void;

    /**
     * Recursively delete a directory.
     *
     * @param string $dirname
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\UnreadableFileException
     * @throws \Viserio\Contract\Finder\Exception\NotFoundException
     *
     * @return void
     */
    public function deleteDirectory(string $dirname): void;

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param string $dirname
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\UnreadableFileException
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     *
     * @return void
     */
    public function cleanDirectory(string $dirname): void;

    /**
     * Determine if the given path is a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function isDirectory(string $dirname): bool;

    /**
     * Copy a directory from one location to another.
     *
     * @param string             $directory
     * @param string             $destination
     * @param array<string, int> $config      An array of boolean options
     *                                        Valid options are:
     *                                        - $config['flags'] @see https://php.net/manual/en/filesystemiterator.construct.php and than the flags section
     *
     * @throws \Viserio\Contract\Filesystem\Exception\IOException
     * @throws \Viserio\Contract\Filesystem\Exception\NotFoundException
     *
     * @return void
     */
    public function copyDirectory(string $directory, string $destination, array $config = []): void;
}
