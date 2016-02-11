<?php
namespace Viserio\Filesystem;

use FilesystemIterator;
use Viserio\Contracts\Filesystem\Exception\FileNotFoundException;
use Viserio\Contracts\Filesystem\Exception\IOException;
use League\Flysystem\Util\MimeType;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use Symfony\Component\Filesystem\Exception\IOException as SymfonyIOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contracts\Filesystem\Directorysystem as DirectorysystemContract;
use Viserio\Filesystem\Traits\FilesystemHelperTrait;
use Symfony\Component\Filesystem\Exception\FileNotFoundException as SymfonyFileNotFoundException;

class Filesystem extends SymfonyFilesystem implements FilesystemContract, DirectorysystemContract
{
    use FilesystemHelperTrait;

    /**
     * @var array
     */
    protected $permissions = [
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

        // @TODO throw new FileNotFoundException($path);
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
        } catch (SymfonyIOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($originFile, $targetFile, $override = false)
    {
        $from = $this->getDirectorySeparator($originFile);
        $to   = $this->getDirectorySeparator($targetFile);

        try {
            return parent::copy($from, $to, $override);
        } catch (SymfonyFileNotFoundException $exception) {
            throw FileNotFoundException();
        } catch (SymfonyIOException $exception) {
            throw IOException();
        }
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
        $path = $this->getDirectorySeparator($path);

        if (!$this->isFile($path) && !$this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $explode = explode('.', $path);

        if ($extension = end($explode)) {
            $extension = strtolower($extension);
        }

        return MimeType::detectByFileExtension($extension);
    }

    /**
     * {@inheritdoc}
     */
    public function extension($path)
    {
        $path = $this->getDirectorySeparator($path);

        return (new SplFileInfo($path))->getExtension();
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        $path = $this->getDirectorySeparator($path);

        if (!$this->isFile($path) && !$this->has($path)) {
            throw new FileNotFoundException($path);
        }

    }

    /**
     * {@inheritdoc}
     */
    public function delete($paths)
    {
        $paths = $this->getDirectorySeparator($paths);

        try {
            $this->remove($paths);
        } catch (SymfonyIOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function files($directory)
    {
        $directory = $this->getDirectorySeparator($directory);

        return array_diff(scandir($directory), ['..', '.']);
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles($directory)
    {
        $directory = $this->getDirectorySeparator($directory);
        $recursive = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        $files = [];

        foreach ($recursive as $file) {
            if ($file->isDir()) {
                continue;
            }

            $files[] = $file->getFilename();
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory($dirname)
    {
        $dirname = $this->getDirectorySeparator($dirname);

        try {
            $this->mkdir($dirname);
        } catch (SymfonyIOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function directories($directory)
    {
        $directory = $this->getDirectorySeparator($directory);

        $dirs = [];

        foreach (glob($directory, GLOB_ONLYDIR) as $dir) {
            $dirs[] = $this->getDirectorySeparator($dir);
        }

        return $dirs;
    }

    /**
     * {@inheritdoc}
     */
    public function allDirectories($directory)
    {
        $directory = $this->getDirectorySeparator($directory);
        $recursive = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST,
            RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );

        $dirs = [];

        foreach ($recursive as $dir) {
            if ($dir->isDir()) {
                $dirs[] = $this->getDirectorySeparator($dir->getRealpath());
            }
        }

        return $dirs;
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
        } catch (SymfonyIOException $exception) {
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
     * {@inheritdoc}
     */
    public function getExtension($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
