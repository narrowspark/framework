<?php
namespace Viserio\Filesystem;

use ErrorException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;
use Viserio\Contracts\Filesystem\FileNotFoundException;
use Viserio\Filesystem\Traits\DirectoryTrait;
use Viserio\Filesystem\Traits\MimetypeTrait;

class Filesystem extends SymfonyFilesystem
{
    use DirectoryTrait, MimetypeTrait;

    /**
     * Get the contents of a file.
     *
     * @param string $path
     *
     * @throws \Viserio\Contracts\Filesystem\FileNotFoundException
     *
     * @return string
     */
    public function get($path)
    {
        if ($this->isFile($path)) {
            return file_get_contents($path);
        }

        throw new FileNotFoundException(sprintf('File does not exist at path %s', $path));
    }

    /**
     * Get the returned value of a file.
     *
     * @param string $path
     *
     * @throws \Viserio\Contracts\Filesystem\FileNotFoundException
     *
     * @return string|null
     */
    public function getRequire($path)
    {
        if ($this->isFile($path)) {
            return require $path;
        }

        throw new FileNotFoundException(sprintf('File does not exist at path %s', $path));
    }

    /**
     * Require the given file once.
     *
     * @param string $file
     *
     * @return mixed
     */
    public function requireOnce($file)
    {
        require_once $file;
    }

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @param bool   $lock
     *
     * @return int
     */
    public function put($path, $contents, $lock = false)
    {
        return file_put_contents($path, $contents, $lock ? LOCK_EX : 0);
    }

    /**
     * Prepend to a file.
     *
     * @param string $path
     * @param string $data
     *
     * @return int
     */
    public function prepend($path, $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data . $this->get($path));
        } else {
            return $this->put($path, $data);
        }
    }

    /**
     * Append to a file.
     *
     * @param string $path
     * @param string $data
     *
     * @return int
     */
    public function append($path, $data)
    {
        return file_put_contents($path, $data, FILE_APPEND);
    }

    /**
     * Extract the file extension from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function extension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * Returns the filename without the extension from a file path.
     *
     * @param string      $path      The path string
     * @param string|null $extension If specified, only that extension is cut off
     *                               (may contain leading dot)
     *
     * @return string Filename without extension
     */
    public function withoutExtension($path, $extension = null)
    {
        if ($extension !== null) {
            // remove extension and trailing dot
            return rtrim(basename($path, $extension), '.');
        }

        return pathinfo($path, PATHINFO_FILENAME);
    }

    /**
     * Changes the extension of a path string.
     *
     * @param string $path      The path string with filename.ext to change
     * @param string $extension New extension (with or without leading dot)
     *
     * @return string The path string with new file extension
     */
    public function changeExtension($path, $extension)
    {
        $actualExtension = $this->extension($path);
        $extension = ltrim($extension, '.');

        // No extension for paths
        if (substr($path, -1) === '/') {
            return $path;
        }

        // No actual extension in path
        if (empty($actualExtension)) {
            return $path . (substr($path, -1) === '.' ? '' : '.') . $extension;
        }

        return substr($path, 0, -strlen($actualExtension)) . $extension;
    }

    /**
     * Get the file type of a given file.
     *
     * @param string $path
     *
     * @return string
     */
    public function type($path)
    {
        return filetype($path);
    }

    /**
     * Get the file size of a given file.
     *
     * @param string $path
     *
     * @return int
     */
    public function size($path)
    {
        return filesize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     *
     * @return int
     */
    public function lastModified($path)
    {
        return filemtime($path);
    }

    /**
     * Determine if the given path is writable.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * Determine if the given path is a file.
     *
     * @param string $file
     *
     * @return bool
     */
    public function isFile($file)
    {
        return is_file($file);
    }

    /**
     * Find path names matching a given pattern.
     *
     * @param string $pattern
     * @param int    $flags
     *
     * @return array
     */
    public function glob($pattern, $flags = 0)
    {
        return glob($pattern, $flags);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param string $directory
     *
     * @return array
     */
    public function files($directory)
    {
        $glob = glob($directory . '/*');
        if ($glob === false) {
            return [];
        }
        // To get the appropriate files, we'll simply glob the directory and filter
        // out any "files" that are not truly files so we do not end up with any
        // directories in our list, but only true files within the directory.
        return array_filter($glob, function ($file) {
            return filetype($file) === 'file';
        });
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string   $directory
     * @param string[] $type      Files types
     *
     * @return array
     */
    public function allFiles($directory, $type = null)
    {
        $files = [];

        if (is_dir($directory)) {
            $iterator = new RecursiveDirectoryIterator($directory);

            foreach (new RecursiveIteratorIterator($iterator) as $file) {
                if ($type !== null) {
                    if (is_array($type)) {
                        $file_ext = substr(strrchr($file->getFilename(), '.'), 1);

                        if (in_array($file_ext, $type)) {
                            if (strpos($file->getFilename(), $file_ext, 1)) {
                                if ($file_path) {
                                    $files[] = $file->getPathName();
                                } else {
                                    $files[] = $file->getFilename();
                                }
                            }
                        }
                    } else {
                        if (strpos($file->getFilename(), $type, 1)) {
                            if ($file_path) {
                                $files[] = $file->getPathName();
                            } else {
                                $files[] = $file->getFilename();
                            }
                        }
                    }
                } else {
                    if ($file->getFilename() !== '.' && $file->getFilename() !== '..') {
                        if ($file_path) {
                            $files[] = $file->getPathName();
                        } else {
                            $files[] = $file->getFilename();
                        }
                    }
                }
            }

            return $files;
        }

        return [];
    }

    /**
     * Is file an image?
     *
     * @param string $path
     *
     * @return bool
     */
    public function isImage($path)
    {
        $data = @getimagesize($path);

        if (!$data || !$data[0] || !$data[1]) {
            return false;
        }

        return true;
    }
}
