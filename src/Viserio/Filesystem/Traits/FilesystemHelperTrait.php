<?php
namespace Viserio\Filesystem\Traits;

use Viserio\Contracts\Filesystem\Exception\FileNotFoundException;

trait FilesystemHelperTrait
{
    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function isWritable($path)
    {
        $path = $this->normalizeDirectorySeparator($path);

        return is_writable($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isFile($file)
    {
        $file = $this->normalizeDirectorySeparator($file);

        return is_file($file);
    }

    /**
     * Determine if the given path is a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function isDirectory($dirname)
    {
        $dirname = $this->normalizeDirectorySeparator($dirname);

        return is_dir($dirname);
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
