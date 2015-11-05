<?php
namespace Brainwave\Filesystem\Traits;

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
 * @version     0.10.0-dev
 */

use Symfony\Component\Finder\Finder;

/**
 * MimetypeTrait.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
trait DirectoryTrait
{
    /**
     * Get all of the directories within a given directory.
     *
     * @param string $directory
     *
     * @return array
     */
    public function directories($directory)
    {
        $directories = [];
        foreach (Finder::create()->in($directory)->directories()->depth(0) as $dir) {
            $directories[] = $dir->getPathname();
        }

        return $directories;
    }

    /**
     * Create a directory.
     *
     * @param string $path
     * @param int    $mode
     * @param bool   $recursive
     * @param bool   $force
     *
     * @return bool
     */
    public function makeDirectory($path, $mode = 0755, $recursive = false, $force = false)
    {
        if ($force) {
            return @mkdir($path, $mode, $recursive);
        } else {
            return mkdir($path, $mode, $recursive);
        }
    }

    /**
     * Copy a directory from one location to another.
     *
     * @param string   $directory
     * @param string   $destination
     * @param int|null $options
     *
     * @return bool
     */
    public function copyDirectory($directory, $destination, $options = null)
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }

        $options = $options ?: \FilesystemIterator::SKIP_DOTS;

        // If the destination directory does not actually exist, we will go ahead and
        // create it recursively, which just gets the destination prepared to copy
        // the files over. Once we make the directory we'll proceed the copying.
        if (!$this->isDirectory($destination)) {
            $this->makeDirectory($destination, 0777, true);
        }

        $items = new \FilesystemIterator($directory, $options);

        foreach ($items as $item) {
            // As we spin through items, we will check to see if the current file is actually
            // a directory or a file. When it is actually a directory we will need to call
            // back into this function recursively to keep copying these nested folders.
            $target = $destination.'/'.$item->getBasename();

            if ($item->isDir()) {
                $path = $item->getPathname();

                if (!$this->copyDirectory($path, $target, $options)) {
                    return false;
                }

            // If the current items is just a regular file, we will just copy this to the new
            // location and keep looping. If for some reason the copy fails we'll bail out
            // and return false, so the developer is aware that the copy process failed.
            } else {
                if (!$this->copy($item->getPathname(), $target)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Recursively delete a directory.
     *
     * The directory itself may be optionally preserved.
     *
     * @param string $directory
     * @param bool   $preserve
     *
     * @return bool
     */
    public function deleteDirectory($directory, $preserve = false)
    {
        if (!$this->isDirectory($directory)) {
            return false;
        }

        $items = new \FilesystemIterator($directory);

        foreach ($items as $item) {
            // If the item is a directory, we can just recurse into the function and
            // delete that sub-directory otherwise we'll just delete the file and
            // keep iterating through each file until the directory is cleaned.
            if ($item->isDir() && !$item->isLink()) {
                $this->deleteDirectory($item->getPathname());

            // If the item is just a file, we can go ahead and delete it since we're
            // just looping through and waxing all of the files in this directory
            // and calling directories recursively, so we delete the real path.
            } else {
                $this->delete($item->getPathname());
            }
        }

        if (!$preserve) {
            @rmdir($directory);
        }

        return true;
    }

    /**
     * Empty the specified directory of all files and folders.
     *
     * @param string $directory
     *
     * @return bool
     */
    public function cleanDirectory($directory)
    {
        return $this->deleteDirectory($directory, true);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param string $directory
     *
     * @return bool
     */
    public function isDirectory($directory)
    {
        return is_dir($directory);
    }

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     *
     * @return bool
     */
    abstract public function delete($paths);

    /**
     * Copy a file to a new location.
     *
     * @param string $path
     * @param string $target
     *
     * @return bool
     */
    abstract public function copy($path, $target);
}
