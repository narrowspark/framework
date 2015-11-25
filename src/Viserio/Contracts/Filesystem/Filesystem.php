<?php
namespace Viserio\Contracts\Filesystem;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

/**
 * Filesystem.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
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
     * Determine if a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function exists($path);

    /**
     * Get the contents of a file.
     *
     * @param string $path
     *
     * @throws FileNotFoundException
     *
     * @return array|false
     */
    public function get($path);

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @param array  $configs
     *
     * @return bool
     */
    public function put($path, $contents, array $configs = []);

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
     * Prepend to a file.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function prepend($path, $data);

    /**
     * Append to a file.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function append($path, $data);

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     *
     * @return bool
     */
    public function delete($paths);

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
     * Get the file size of a given file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function size($path);

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function lastModified($path);

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
     * Recursively delete a directory.
     *
     * @param string $directory
     *
     * @return bool
     */
    public function deleteDirectory($directory);
}
