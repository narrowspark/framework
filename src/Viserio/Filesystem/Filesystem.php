<?php
namespace Viserio\Filesystem;

use FilesystemIterator;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Util\MimeType;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Support\Traits\DirectorySeparatorTrait;

class Filesystem extends SymfonyFilesystem implements FilesystemContract
{
    use DirectorySeparatorTrait;

    /**
     * @var array
     */
    protected static $permissions = [
        'file' => [
            'public'  => 0744,
            'private' => 0700,
        ],
        'dir' => [
            'public'  => 0755,
            'private' => 0700,
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function has($path)
    {
        $path = $this->getDirectorySeparator($path);

        return $this->exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
    {
        $path = $this->getDirectorySeparator($path);

        if ($this->isFile($path) && $this->has($path)) {
            return file_get_contents($path);
        }

        throw new FileNotFoundException($path);
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, array $config = [])
    {
        $path = $this->getDirectorySeparator($path);
        $lock = isset($config['lock']) ? LOCK_EX : 0;

        if (file_put_contents($path, $contents, $lock) === false) {
            return false;
        }

        if (isset($config['visibility'])) {
            $this->setVisibility($path, $config['visibility']);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, array $config = [])
    {
        $path = $this->getDirectorySeparator($path);

        return file_put_contents($path, $contents, FILE_APPEND);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        $path = $this->getDirectorySeparator($path);

        clearstatcache(false, $path);
        $permissions = octdec(substr(sprintf('%o', fileperms($path)), -4));

        return $permissions & 0044 ?
            FilesystemContract::VISIBILITY_PUBLIC :
            FilesystemContract::VISIBILITY_PRIVATE;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility($path, $visibility)
    {
        $path = $this->getDirectorySeparator($path);
        $visibility = $this->parseVisibility($visibility);

        try {
            $this->chmod($path, $visibility);
        } catch (IOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($from, $to)
    {
        $from = $this->getDirectorySeparator($from);
        $to   = $this->getDirectorySeparator($to);

        return parent::copy($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function move($from, $to)
    {
        $from = $this->getDirectorySeparator($from);
        $to   = $this->getDirectorySeparator($to);

        return rename($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $path = $this->getDirectorySeparator($path);

        return filesize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
        $path    = $this->getDirectorySeparator($path);
        $explode = explode('.', $path);

        if ($extension = end($explode)) {
            $extension = strtolower($extension);
        }

        return MimeType::detectByFileExtension($extension);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        $path = $this->getDirectorySeparator($path);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($paths)
    {
        $paths = $this->getDirectorySeparator($paths);

        try {
            $this->remove($directories);
        } catch (IOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function files($directory = null, $recursive = false)
    {
        $directory = $this->getDirectorySeparator($directory);
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles($directory = null, $recursive = false)
    {
        $directory = $this->getDirectorySeparator($directory);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory($dirname)
    {
        $dirname = $this->getDirectorySeparator($dirname);

        try {
            $this->mkdir($dirname);
        } catch (IOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function directories($directory = null, $recursive = false)
    {
        $directory = $this->getDirectorySeparator($directory);
    }

    /**
     * {@inheritdoc}
     */
    public function allDirectories($directory = null)
    {
        $directory = $this->getDirectorySeparator($directory);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory($dirname)
    {
        $dirname = $this->getDirectorySeparator($dirname);

        if (!$this->isDirectory($dirname)) {
            return false;
        }

        try {
            $this->remove($dirname);
        } catch (IOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanDirectory($dirname)
    {
        $dirname = $this->getDirectorySeparator($dirname);

        if (!$this->isDirectory($dirname)) {
            return false;
        }

        $directories = new FilesystemIterator($dirname);

        foreach ($directories as $dirname) {
            @rmdir($dirname);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory($dirname)
    {
        $dirname = $this->getDirectorySeparator($dirname);

        return is_dir($dirname);
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
        $path = $this->getDirectorySeparator($path);

        if ($this->isFile($path) && $this->has($path)) {
            return require $path;
        }

        throw new FileNotFoundException($path);
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
        $path = $this->getDirectorySeparator($path);

        if ($this->isFile($path) && $this->has($path)) {
            require_once $file;
        }

        throw new FileNotFoundException($path);
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
        $path = $this->getDirectorySeparator($path);

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
        $file = $this->getDirectorySeparator($file);

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
     * Extract the file extension from a file path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getExtension($path)
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
        $path = $this->getDirectorySeparator($path);

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
        $path    = $this->getDirectorySeparator($path);
        $explode = explode('.', $path);

        if ($actualExtension = end($explode)) {
            $actualExtension = strtolower($extension);
        }

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
     * Parse the given visibility value.
     *
     * @param string      $path
     * @param string|null $visibility
     *
     * @throws \InvalidArgumentException
     *
     * @return int
     */
    private function parseVisibility($path, $visibility)
    {
        $type = '';

        if (is_file($path)) {
            $type = 'file';
        } elseif (is_dir($path)) {
            $type = 'dir';
        }

        if ($visibility === null || $type === '') {
            return;
        }

        switch ($visibility) {
            case FilesystemContract::VISIBILITY_PUBLIC:
                return $this->permissions[$type][$visibility];

            case FilesystemContract::VISIBILITY_PRIVATE:
                return $this->permissions[$type][$visibility];
        }

        throw new InvalidArgumentException('Unknown visibility: ' . $visibility);
    }
}
