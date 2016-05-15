<?php
namespace Viserio\Filesystem\Traits;

use Viserio\Contracts\Filesystem\Exception\FileNotFoundException;

trait FilesystemHelperTrait
{
    /**
     * Require file.
     *
     * @param string $file
     *
     * @throws Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     *
     * @return mixed
     */
    public function getRequire($path)
    {
        $path = $this->normalizeDirectorySeparator($path);

        if ($this->isFile($path) && file_exists($path)) {
            return require $path;
        }

        throw new FileNotFoundException($path);
    }

    /**
     * Require file once.
     *
     * @param string $file
     *
     * @throws Viserio\Contracts\Filesystem\Exception\FileNotFoundException
     *
     * @return mixed
     */
    public function requireOnce($file)
    {
        $path = $this->normalizeDirectorySeparator($path);

        if ($this->isFile($path) && file_exists($path)) {
            require_once $file;
        }

        throw new FileNotFoundException($path);
    }

    /**
     * Check if path is writable.
     *
     * @param string $path
     *
     * @return boolean
     */
    public function isWritable($path)
    {
        $path = $this->normalizeDirectorySeparator($path);

        return is_writable($path);
    }

    /**
     * Check if path is a file.
     *
     * @param string $file
     *
     * @return bool
     */
    public function isFile($file)
    {
        $file = $this->normalizeDirectorySeparator($file);

        return is_file($file);
    }

    /**
     * Fix directory separators for windows and linux
     *
     * @param string|array $paths
     *
     * @return string|array
     */
    abstract protected function normalizeDirectorySeparator($paths);
}
