<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Traits;

use Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException;

trait FilesystemHelperTrait
{
    /**
     * Require file.
     *
     * @param string $path
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return mixed
     */
    public function getRequire(string $path)
    {
        $path = $this->getTransformedPath($path);

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
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function requireOnce(string $path)
    {
        $path = $this->getTransformedPath($path);

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
        return \is_writable($this->getTransformedPath($path));
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
        return \is_file($this->getTransformedPath($file));
    }

    /**
     * Create a hard link to the target file or directory.
     *
     * @param string $target
     * @param string $link
     *
     * @return null|bool
     *
     * @codeCoverageIgnore
     */
    public function link(string $target, string $link): ?bool
    {
        $target = $this->getTransformedPath($target);
        $link   = $this->getTransformedPath($link);

        if (! $this->isWindows()) {
            return \symlink($target, $link);
        }

        $mode   = $this->isDirectory($target) ? 'J' : 'H';
        $output = $return = false;

        \exec("mklink /{$mode} \"{$link}\" \"{$target}\"", $output, $return);

        return $return;
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
     * Determine whether the current environment is Windows based.
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function isWindows(): bool
    {
        return \mb_stripos(\PHP_OS, 'win') === 0;
    }

    /**
     * Transforms the path.
     *
     * @param string $path
     *
     * @return string
     */
    abstract protected function getTransformedPath(string $path): string;
}
