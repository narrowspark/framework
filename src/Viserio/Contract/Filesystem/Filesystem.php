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

interface Filesystem extends Directorysystem
{
    /**
     * The public visibility setting.
     *
     * @var string
     */
    public const VISIBILITY_PUBLIC = 'public';

    /**
     * The private visibility setting.
     *
     * @var string
     */
    public const VISIBILITY_PRIVATE = 'private';

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has(string $path): bool;

    /**
     * Read a file.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return false|string the file contents or false on failure
     */
    public function read(string $path);

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return false|resource the file contents or false on failure
     */
    public function readStream(string $path);

    /**
     * Write a new file.
     *
     * @param string $path     the path of the new file
     * @param string $contents the file contents
     * @param array  $config   an optional configuration array
     *
     * @return bool true on success, false on failure
     */
    public function write(string $path, string $contents, array $config = []): bool;

    /**
     * Write a new file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param array    $config   an optional configuration array
     *
     * @return bool
     */
    public function writeStream(string $path, $resource, array $config = []): bool;

    /**
     * Write the contents of a file.
     *
     * @param string          $path
     * @param resource|string $contents
     * @param array           $config   an optional configuration array
     *
     * @return bool
     */
    public function put(string $path, $contents, array $config = []): bool;

    /**
     * Append existing file or create new.
     *
     * @param string $path
     * @param string $contents
     * @param array  $config   an optional configuration array
     *
     * @return bool true on success, false on failure
     */
    public function append(string $path, string $contents, array $config = []): bool;

    /**
     * Append existing file or create new using stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param array    $config   an optional configuration array
     *
     * @return bool true on success, false on failure
     */
    public function appendStream(string $path, $resource, array $config = []): bool;

    /**
     * Update an existing file.
     *
     * @param string $path     the path of the existing file
     * @param string $contents the file contents
     * @param array  $config   an optional configuration array
     *
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return bool true on success, false on failure
     */
    public function update(string $path, string $contents, array $config = []): bool;

    /**
     * Update a file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param array    $config
     *
     * @return bool
     */
    public function updateStream(string $path, $resource, array $config = []): bool;

    /**
     * Get the visibility for the given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getVisibility(string $path): string;

    /**
     * Set the visibility for the given path.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return bool
     */
    public function setVisibility(string $path, string $visibility): bool;

    /**
     * Copies a file.
     *
     * This method only copies the file if the origin file is newer than the target file.
     *
     * By default, if the target already exists, it is not overridden.
     *
     * @param string $originFile The original filename
     * @param string $targetFile The target filename
     * @param bool   $override   Whether to override an existing file or not
     *
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException When originFile doesn't exist
     * @throws \Viserio\Contract\Filesystem\Exception\IOException           When copy fails
     *
     * @return bool
     */
    public function copy($originFile, $targetFile, $override = false): bool;

    /**
     * Move a file to a new location.
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function move(string $from, string $to): bool;

    /**
     * Get a file's size.
     *
     * @param string $path the path to the file
     *
     * @return bool|int the file size or false on failure
     */
    public function getSize(string $path);

    /**
     * Get a file's mime-type.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return bool|string the file mime-type or false on failure
     */
    public function getMimetype(string $path);

    /**
     * Get a file's timestamp.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return bool|string the timestamp or false on failure
     */
    public function getTimestamp(string $path);

    /**
     * Get the URL for the file at the given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function url(string $path): string;

    /**
     * Delete the file at a given path.
     *
     * @param string|string[] $paths
     *
     * @return bool
     */
    public function delete($paths): bool;

    /**
     * Get an array of all files in a directory.
     *
     * @param string $directory
     *
     * @return array
     */
    public function files(string $directory): array;

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string $directory
     * @param bool   $showHiddenFiles
     *
     * @return array
     */
    public function allFiles(string $directory, bool $showHiddenFiles = false): array;

    /**
     * Extract the file extension from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getExtension(string $path): string;

    /**
     * Returns the filename without the extension from a file path.
     *
     * @param string      $path      The path string
     * @param null|string $extension If specified, only that extension is cut off
     *                               (may contain leading dot)
     *
     * @return string Filename without extension
     */
    public function withoutExtension(string $path, ?string $extension = null): string;

    /**
     * Changes the extension of a path string.
     *
     * @param string $path      The path string with filename.ext to change
     * @param string $extension New extension (with or without leading dot)
     *
     * @return string The path string with new file extension
     */
    public function changeExtension(string $path, string $extension): string;
}
