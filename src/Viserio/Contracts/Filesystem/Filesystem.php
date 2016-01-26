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
    public function has($path);

    /**
     * Read a file.
     *
     * @param string $path The path to the file.
     *
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return string|false The file contents or false on failure.
     */
    public function read($path);

    /**
     * Write a new file.
     *
     * @param string $path     The path of the new file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @return bool True on success, false on failure.
     */
    public function write($path, $contents, array $config = []);

    /**
     * Update an existing file.
     *
     * @param string $path     The path of the existing file.
     * @param string $contents The file contents.
     * @param array  $config   An optional configuration array.
     *
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return bool True on success, false on failure.
     */
    public function update($path, $contents, array $config = []);

    /**
     * Get the visibility for the given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getVisibility($path);

    /**
     * Set the visibility for the given path.
     *
     * @param string $path
     * @param string $visibility
     */
    public function setVisibility($path, $visibility);

    /**
     * Copy a file to a new location.
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function copy($from, $to);

    /**
     * Move a file to a new location.
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function move($from, $to);

    /**
     * Get a file's size.
     *
     * @param string $path The path to the file.
     *
     * @return int|false The file size or false on failure.
     */
    public function getSize($path);

    /**
     * Get a file's mime-type.
     *
     * @param string $path The path to the file.
     *
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return string|false The file mime-type or false on failure.
     */
    public function getMimetype($path);

    /**
     * Get a file's timestamp.
     *
     * @param string $path The path to the file.
     *
     * @throws \League\Flysystem\FileNotFoundException
     *
     * @return string|false The timestamp or false on failure.
     */
    public function getTimestamp($path);

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     *
     * @return bool
     */
    public function delete($paths);

    /**
     * Get an array of all files in a directory.
     *
     * @param string|null $directory
     * @param bool        $recursive
     *
     * @return array
     */
    public function files($directory = null, $recursive = false);

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string|null $directory
     *
     * @return array
     */
    public function allFiles($directory = null);

    /**
     * Get all of the directories within a given directory.
     *
     * @param string|null $directory
     * @param bool        $recursive
     *
     * @return array
     */
    public function directories($directory = null, $recursive = false);

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param string|null $directory
     *
     * @return array
     */
    public function allDirectories($directory = null);

    /**
     * Recursively create a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function createDirectory($dirname);

    /**
     * Recursively delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDirectory($dirname);

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function cleanDirectory($dirname);

    /**
     * Determine if the given path is a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function isDirectory($dirname);
}
