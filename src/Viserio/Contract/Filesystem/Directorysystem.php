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

interface Directorysystem
{
    /**
     * Get all of the directories within a given directory.
     *
     * @param string $directory
     *
     * @return array
     */
    public function directories(string $directory): array;

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param string $directory
     *
     * @return array
     */
    public function allDirectories(string $directory): array;

    /**
     * Recursively create a directory.
     *
     * @param string $dirname
     * @param array  $config
     *
     * @return bool
     */
    public function createDirectory(string $dirname, array $config = []): bool;

    /**
     * Recursively delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDirectory(string $dirname): bool;

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function cleanDirectory(string $dirname): bool;

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
     * @param string $directory
     * @param string $destination
     * @param array  $options
     *
     * @return bool
     */
    public function copyDirectory(string $directory, string $destination, array $options = []): bool;

    /**
     * Move a directory.
     *
     * @param string $directory
     * @param string $destination
     * @param array  $options
     *
     * @return bool
     */
    public function moveDirectory(string $directory, string $destination, array $options = []): bool;
}
