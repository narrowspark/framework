<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem;

use FilesystemIterator;
use InvalidArgumentException;
use League\Flysystem\Util;
use League\Flysystem\Util\MimeType;
use Symfony\Component\Filesystem\Exception\FileNotFoundException as SymfonyFileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException as SymfonyIOException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;
use Viserio\Component\Contracts\Filesystem\Directorysystem as DirectorysystemContract;
use Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException;
use Viserio\Component\Contracts\Filesystem\Exception\IOException as ViserioIOException;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Filesystem\Traits\FilesystemExtensionTrait;
use Viserio\Component\Filesystem\Traits\FilesystemHelperTrait;
use Viserio\Component\Support\Traits\MacroableTrait;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class Filesystem extends SymfonyFilesystem implements FilesystemContract, DirectorysystemContract
{
    use NormalizePathAndDirectorySeparatorTrait;
    use FilesystemHelperTrait;
    use FilesystemExtensionTrait;
    use MacroableTrait;

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
        $path = self::normalizeDirectorySeparator($path);

        return $this->exists($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path)
    {
        $path = self::normalizeDirectorySeparator($path);

        if ($this->isFile($path) && $this->has($path)) {
            return file_get_contents($path);
        }

        throw new FileNotFoundException($path);
    }

    /**
     * {@inheritdoc}
     */
    public function readStream(string $path)
    {
        $path = self::normalizeDirectorySeparator($path);

        if (! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $stream = @fopen($path, 'rb');

        return $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, string $contents, array $config = []): bool
    {
        $path = self::normalizeDirectorySeparator($path);
        $lock = isset($config['lock']) ? LOCK_EX : 0;

        if (! is_int(@file_put_contents($path, $contents, $lock))) {
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
    public function writeStream(string $path, $resource, array $config = []): bool
    {
        Util::rewindStream($resource);

        $contents = stream_get_contents($resource);

        return $this->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, $contents, array $config = []): bool
    {
        $path = self::normalizeDirectorySeparator($path);
        $lock = isset($config['lock']) ? LOCK_EX : 0;

        if (is_resource($contents)) {
            return $this->writeStream($path, $contents, $config);
        }

        return is_int(@file_put_contents($path, $contents, $lock));
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $path, string $contents, array $config = []): bool
    {
        $path = self::normalizeDirectorySeparator($path);

        if (! $this->exists($path)) {
            throw new FileNotFoundException($path);
        }

        return is_int(@file_put_contents($path, $contents));
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream(string $path, $resource, array $config = []): bool
    {
        Util::rewindStream($resource);

        $contents = stream_get_contents($resource);

        return $this->update($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility(string $path): string
    {
        $path = self::normalizeDirectorySeparator($path);
        clearstatcache(false, $path);
        $permissions = octdec(mb_substr(sprintf('%o', fileperms($path)), -4));

        return $permissions & 0044 ?
            FilesystemContract::VISIBILITY_PUBLIC :
            FilesystemContract::VISIBILITY_PRIVATE;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility(string $path, string $visibility): bool
    {
        $path       = self::normalizeDirectorySeparator($path);
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
        $from = self::normalizeDirectorySeparator($originFile);
        $to   = self::normalizeDirectorySeparator($targetFile);

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
        $from = self::normalizeDirectorySeparator($from);
        $to   = self::normalizeDirectorySeparator($to);

        return rename($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(string $path)
    {
        $path = self::normalizeDirectorySeparator($path);

        return filesize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype(string $path)
    {
        $path = self::normalizeDirectorySeparator($path);

        if (! $this->isFile($path) && ! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $explode = explode('.', $path);

        if ($extension = end($explode)) {
            $extension = mb_strtolower($extension);
        }

        return MimeType::detectByFileExtension($extension);
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp(string $path)
    {
        $path = self::normalizeDirectorySeparator($path);

        if (! $this->isFile($path) && ! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        return date('F d Y H:i:s', filemtime($path));
    }

    /**
     * {@inheritdoc}
     */
    public function url(string $path): string
    {
        return self::normalizeDirectorySeparator($path);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $paths): bool
    {
        $paths = self::normalizeDirectorySeparator($paths);

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
        $directory = self::normalizeDirectorySeparator($directory);

        $files = array_diff(scandir($directory), ['..', '.']);

        // To get the appropriate files, we'll simply scan the directory and filter
        // out any "files" that are not truly files so we do not end up with any
        // directories in our list, but only true files within the directory.
        return array_filter($files, function ($file) use ($directory) {
            return filetype(self::normalizeDirectorySeparator($directory . '/' . $file)) == 'file';
        });
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles(string $directory, bool $showHiddenFiles = false): array
    {
        $files  = [];
        $finder = Finder::create()->files()->ignoreDotFiles(! $showHiddenFiles)->in($directory);

        foreach ($finder as $dir) {
            $files[] = self::normalizeDirectorySeparator($dir->getPathname());
        }

        return $files;
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(string $dirname, array $config = []): bool
    {
        $dirname = self::normalizeDirectorySeparator($dirname);
        $mode    = $this->permissions['dir']['public'];

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
            $directories[] = self::normalizeDirectorySeparator($dir->getPathname());
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
        $dirname = self::normalizeDirectorySeparator($dirname);

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
        $dirname = self::normalizeDirectorySeparator($dirname);

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
        $dirname = self::normalizeDirectorySeparator($dirname);

        return is_dir($dirname);
    }

    /**
     * {@inheritdoc}
     */
    public function copyDirectory(string $directory, string $destination, array $options = []): bool
    {
        $directory = self::normalizeDirectorySeparator($directory);

        if (! $this->isDirectory($directory)) {
            return false;
        }

        $destination = self::normalizeDirectorySeparator($destination);

        try {
            $this->mirror($directory, $destination, null, $options);
        } catch (SymfonyIOException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function moveDirectory(string $directory, string $destination, array $options = []): bool
    {
        $directory   = self::normalizeDirectorySeparator($directory);
        $destination = self::normalizeDirectorySeparator($destination);
        $overwrite   = $options['overwrite'] ?? false;

        if ($overwrite && $this->isDirectory($destination)) {
            if (! $this->deleteDirectory($destination)) {
                return false;
            }
        }

        if (@rename($directory, $destination) !== true) {
            return false;
        }

        return true;
    }

    /**
     * Get normalize or prefixed path.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getNormalizedOrPrefixedPath(string $path): string
    {
        return self::normalizeDirectorySeparator($path);
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
