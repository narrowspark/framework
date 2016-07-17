<?php
namespace Viserio\Contracts\Filesystem;

interface Filesystem
{
    /**
     * The public visibility setting.
     *
     * @var string
     */
    const VISIBILITY_PUBLIC = 'public';

    /**
     * The private visibility setting.
     *
     * @var string
     */
    const VISIBILITY_PRIVATE = 'private';

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
     * @param string $path The path to the file.
     *
     * @throws \Viserio\Contracts\Filesystem\FileNotFoundException
     *
     * @return string|false The file contents or false on failure.
     */
    public function read(string $path);

    /**
     * Write a new file.
     *
     * @param string $path     The path of the new file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @return bool True on success, false on failure.
     */
    public function write(string $path, string $contents, array $config = []): bool;

    /**
     * Update an existing file.
     *
     * @param string $path     The path of the existing file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws \Viserio\Contracts\Filesystem\FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function update(string $path, string $contents, array $config = []): bool;

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
     * @throws \Viserio\Contracts\Filesystem\Exception\FileNotFoundException When originFile doesn't exist
     * @throws \Viserio\Contracts\Filesystem\Exception\IOException           When copy fails
     *
     * @return bool
     */
    public function copy($originFile, $targetFile, $override = false);

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
     * @param string $path The path to the file.
     *
     * @return int|false The file size or false on failure.
     */
    public function getSize(string $path);

    /**
     * Get a file's mime-type.
     *
     * @param string $path The path to the file.
     *
     * @throws \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     *
     * @return string|false The file mime-type or false on failure.
     */
    public function getMimetype(string $path);

    /**
     * Get a file's timestamp.
     *
     * @param string $path The path to the file.
     *
     * @throws \Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     *
     * @return string|false The timestamp or false on failure.
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
     * @param string[] $paths
     *
     * @return bool
     */
    public function delete(array $paths): bool;

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
     * @param string|null $extension If specified, only that extension is cut off
     *                               (may contain leading dot)
     *
     * @return string Filename without extension
     */
    public function withoutExtension(string $path, string $extension = null): string;

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
