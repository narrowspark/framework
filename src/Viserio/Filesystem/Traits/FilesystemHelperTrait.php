<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Traits;

use Viserio\Contracts\Filesystem\Exception\FileNotFoundException;

trait FilesystemHelperTrait
{
    /**
     * Require file.
     *
     * @param string $path
     *
     * @throws Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     *
     * @return mixed
     */
    public function getRequire(string $path)
    {
        if (isset($this->driver)) {
            $path = $this->driver->getPathPrefix() . $path;
        } else {
            $path = self::normalizeDirectorySeparator($path);
        }

        if ($this->isFile($path) && $this->has($path)) {
            return require $path;
        }

        throw new FileNotFoundException($path);
    }

    /**
     * Require file once.
     *
     * @param string $path
     *
     * @throws Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function requireOnce(string $path)
    {
        if (isset($this->driver)) {
            $path = $this->driver->getPathPrefix() . $path;
        } else {
            $path = self::normalizeDirectorySeparator($path);
        }

        if ($this->isFile($path) && $this->has($path)) {
            require_once $path;
        }

        throw new FileNotFoundException($path);
    }

    /**
     * Check if path is writable.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable(string $path): bool
    {
        if (isset($this->driver)) {
            $path = $this->driver->getPathPrefix() . $path;
        } else {
            $path = self::normalizeDirectorySeparator($path);
        }

        return is_writable($path);
    }

    /**
     * Check if path is a file.
     *
     * @param string $file
     *
     * @return bool
     */
    public function isFile(string $file): bool
    {
        if (isset($this->driver)) {
            $file = $this->driver->getPathPrefix() . $file;
        } else {
            $file = self::normalizeDirectorySeparator($file);
        }

        return is_file($file);
    }

    /**
     * Create a hard link to the target file or directory.
     *
     * @param string $target
     * @param string $link
     *
     * @return bool|null
     *
     * @codeCoverageIgnore
     */
    public function link(string $target, string $link)
    {
        if (isset($this->driver)) {
            $target = $this->driver->getPathPrefix() . $target;
            $link = $this->driver->getPathPrefix() . $link;
        } else {
            $target = self::normalizeDirectorySeparator($target);
            $link = self::normalizeDirectorySeparator($link);
        }

        if (! $this->isWindows()) {
            return symlink($target, $link);
        }

        $mode = $this->isDirectory($target) ? 'J' : 'H';

        exec("mklink /{$mode} \"{$link}\" \"{$target}\"");
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    abstract public function has(string $path): bool;

    /**
     * Determine if the given path is a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    abstract public function isDirectory(string $dirname): bool;

    /**
     * Fix directory separators for windows and linux
     *
     * @param string|array $paths
     *
     * @return string|array
     */
    abstract protected function normalizeDirectorySeparator($paths);

    /**
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function isWindows(): bool
    {
        return strtolower(substr(PHP_OS, 0, 3)) === 'win';
    }
}
