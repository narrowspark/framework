<?php
namespace Viserio\Contracts\Filesystem;

interface Directorysystem
{
    /**
     * Get all of the directories within a given directory.
     *
     * @param string $directory
     *
     * @return array
     */
    public function directories($directory);

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param string|null $directory
     *
     * @return array
     */
    public function allDirectories($directory);

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
