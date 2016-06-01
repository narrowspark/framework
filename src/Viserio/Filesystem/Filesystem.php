<?php
namespace Viserio\Filesystem;

use FilesystemIterator;
use InvalidArgumentException;
use League\Flysystem\Util\MimeType;
use Symfony\Component\Filesystem\Exception\FileNotFoundException as SymfonyFileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException as SymfonyIOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;
use Viserio\Contracts\Filesystem\Directorysystem as DirectorysystemContract;
use Viserio\Contracts\Filesystem\Exception\FileNotFoundException;
use Viserio\Contracts\Filesystem\Exception\IOException as ViserioIOException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Filesystem\Traits\FilesystemExtensionTrait;
use Viserio\Filesystem\Traits\FilesystemHelperTrait;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class Filesystem extends SymfonyFilesystem implements FilesystemContract, DirectorysystemContract
{
    use NormalizePathAndDirectorySeparatorTrait;
    use FilesystemHelperTrait;
    use FilesystemExtensionTrait;

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
    public function has(string $path): bool
    {
        $path = $this->normalizeDirectorySeparator($path);

        return $this->exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path)
    {
        $path = $this->normalizeDirectorySeparator($path);

        if ($this->isFile($path) && $this->has($path)) {
            return file_get_contents($path);
        }

        throw new FileNotFoundException($path);
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, string $contents, array $config = []): bool
    {
        $path = $this->normalizeDirectorySeparator($path);
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
    public function update(string $path, string $contents, array $config = []): bool
    {
        $path = $this->normalizeDirectorySeparator($path);

        if (! $this->exists($path)) {
            throw new FileNotFoundException($path);
        }

        return file_put_contents($path, $contents, FILE_APPEND);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility(string $path): string
    {
        $path = $this->normalizeDirectorySeparator($path);
        clearstatcache(false, $path);
        $permissions = octdec(substr(sprintf('%o', fileperms($path)), -4));

        return $permissions & 0044 ?
            FilesystemContract::VISIBILITY_PUBLIC :
            FilesystemContract::VISIBILITY_PRIVATE;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility(string $path, string $visibility): bool
    {
        $path = $this->normalizeDirectorySeparator($path);
        $visibility = $this->parseVisibility($path, $visibility);

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
    public function copy($originFile, $targetFile, $override = false): bool
    {
        $from = $this->normalizeDirectorySeparator($originFile);
        $to = $this->normalizeDirectorySeparator($targetFile);

        try {
            parent::copy($from, $to, $override);
        } catch (SymfonyFileNotFoundException $exception) {
            throw new FileNotFoundException($exception->getMessage());
        } catch (SymfonyIOException $exception) {
            throw new ViserioIOException($exception->getMessage());
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $from, string $to): bool
    {
        $from = $this->normalizeDirectorySeparator($from);
        $to = $this->normalizeDirectorySeparator($to);

        return rename($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(string $path)
    {
        $path = $this->normalizeDirectorySeparator($path);

        return filesize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype(string $path)
    {
        $path = $this->normalizeDirectorySeparator($path);

        if (! $this->isFile($path) && ! $this->has($path)) {
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
    public function getTimestamp(string $path)
    {
        $path = $this->normalizeDirectorySeparator($path);

        if (! $this->isFile($path) && ! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        return date('F d Y H:i:s', filemtime($path));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $paths): bool
    {
        $paths = $this->normalizeDirectorySeparator($paths);

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
    public function files(string $directory): array
    {
        $directory = $this->normalizeDirectorySeparator($directory);

        return array_diff(scandir($directory), ['..', '.']);
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles(string $directory, bool $showHiddenFiles = false): array
    {
        $files = [];
        $finder = Finder::create()->files()->ignoreDotFiles(! $showHiddenFiles)->in($directory);

        foreach ($finder as $dir) {
            $files[] = $this->normalizeDirectorySeparator($dir->getPathname());
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(string $dirname, array $config = []): bool
    {
        $dirname = $this->normalizeDirectorySeparator($dirname);
        $mode = $this->permissions['dir']['public'];

        if (isset($config['visibility'])) {
            $mode = $this->permissions['dir'][$config['visibility']];
        }

        try {
            $this->mkdir($dirname, $mode);
        } catch (SymfonyIOException $exception) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function directories(string $directory): array
    {
        $directories = [];

        foreach (Finder::create()->in($directory)->directories()->depth(0) as $dir) {
            $directories[] = $this->normalizeDirectorySeparator($dir->getPathname());
        }

        return $directories;
    }

    /**
     * {@inheritdoc}
     */
    public function allDirectories(string $directory): array
    {
        return iterator_to_array(Finder::create()->directories()->in($directory), false);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $dirname): bool
    {
        $dirname = $this->normalizeDirectorySeparator($dirname);

        if (! $this->isDirectory($dirname)) {
            return false;
        }

        $this->remove($dirname);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function cleanDirectory(string $dirname): bool
    {
        $dirname = $this->normalizeDirectorySeparator($dirname);

        if (! $this->isDirectory($dirname)) {
            return false;
        }

        $items = new FilesystemIterator($dirname);

        foreach ($items as $item) {
            if ($item->isDir() && ! $item->isLink()) {
                $this->cleanDirectory($item->getPathname());
            } else {
                try {
                    $this->remove($item->getPathname());
                } catch (SymfonyIOException $exception) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory(string $dirname): bool
    {
        $dirname = $this->normalizeDirectorySeparator($dirname);

        return is_dir($dirname);
    }

    /**
     * Parse the given visibility value.
     *
     * @param string      $path
     * @param string|null $visibility
     *
     * @throws \InvalidArgumentException
     *
     * @return int|null
     */
    private function parseVisibility(string $path, string $visibility = null)
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
