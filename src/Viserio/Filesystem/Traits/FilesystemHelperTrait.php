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
        $path = $this->getDirectorySeparator($path);

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
        $path = $this->getDirectorySeparator($path);

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
        $path = $this->getDirectorySeparator($path);

        return is_writable($path);
    }

    /**
     * {@inheritdoc}
     */
    public function isFile($file)
    {
        $file = $this->getDirectorySeparator($file);

        return is_file($file);
    }

    /**
     * Fix directory separators for windows and linux
     *
     * @param string|array $paths
     *
     * @return string|array
     */
    abstract protected function getDirectorySeparator($paths);
}
