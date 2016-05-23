<?php
namespace Viserio\Filesystem;

use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config as FlyConfig;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Filesystem\Directorysystem as DirectorysystemContract;
use Viserio\Contracts\Filesystem\Exception\FileNotFoundException;
use Viserio\Contracts\Filesystem\Exception\IOException as ViserioIOException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Filesystem\Traits\FilesystemExtensionTrait;

class FilesystemAdapter implements FilesystemContract, DirectorysystemContract
{
    use FilesystemExtensionTrait;

    /**
     * The Flysystem filesystem implementation.
     *
     * @var \League\Flysystem\AdapterInterface
     */
    protected $driver;

    /**
     * Create a new filesystem adapter instance.
     *
     * @param \League\Flysystem\AdapterInterface $driver
     */
    public function __construct(AdapterInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $path): bool
    {
        return $this->driver->has($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path): string
    {
        if (!$this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $content = $this->driver->read($path);

        return !$content ?: $content['contents'];
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, string $contents, array $config = []): bool
    {
        $visibility = isset($configs['visibility']) ? $configs['visibility'] : null;

        $configs['visibility'] = $this->parseVisibility($visibility) ?: [];

        $flyConfig = new FlyConfig($configs);

        if (is_resource($contents)) {
            return $this->driver->writeStream($path, $contents, $flyConfig);
        }

        return $this->driver->write($path, $contents, $flyConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $path, string $contents, array $config = []): string
    {
        if (!$this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $flyConfig = new FlyConfig($config);

        return $this->driver->update($path, $contents, $flyConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility(string $path)
    {
        $visibility = $this->driver->getVisibility($path);

        if (isset($visibility['visibility']) && $visibility['visibility'] === AdapterInterface::VISIBILITY_PUBLIC) {
            return FilesystemContract::VISIBILITY_PUBLIC;
        }

        return FilesystemContract::VISIBILITY_PRIVATE;
    }

    /**
     * {@inheritdoc}
     */
    public function setVisibility(string $path, $visibility)
    {
        $this->driver->setVisibility($path, $this->parseVisibility($visibility));
    }

    /**
     * {@inheritdoc}
     */
    public function copy(string $originFile, string $targetFile, bool $override = false)
    {
        if (!$this->has($originFile)) {
            throw new FileNotFoundException($originFile);
        }

        $orginal = $this->driver->applyPathPrefix($originFile);
        $target  = $this->driver->applyPathPrefix($targetFile);

        // https://bugs.php.net/bug.php?id=64634
        if (@fopen($orginal, 'r') === false) {
            throw new ViserioIOException(sprintf('Failed to copy "%s" to "%s" because source file could not be opened for reading.', $orginal, $target), 0, null, $orginal);
        }

        // Stream context created to allow files overwrite when using FTP stream wrapper - disabled by default
        if (@fopen($target, 'w', null, stream_context_create(['ftp' => ['overwrite' => true]])) === false) {
            throw new ViserioIOException(sprintf('Failed to copy "%s" to "%s" because target file could not be opened for writing.', $orginal, $target), 0, null, $orginal);
        }

        $this->driver->copy($originFile, $targetFile);

        if (!is_file($target)) {
            throw new ViserioIOException(sprintf('Failed to copy "%s" to "%s".', $originFile, $target), 0, null, $originFile);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $from, string $to)
    {
        $this->driver->rename($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(string $path)
    {
        $size = $this->driver->getSize($path);

        return !$size ?: $size['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype(string $path)
    {
        if (!$this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $mimetype = $this->driver->getMimetype($path);

        return !$mimetype ?: $mimetype['mimetype'];
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp(string $path)
    {
        if (!$this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $getTimestamp = $this->driver->getTimestamp($path);

        return !$getTimestamp ?: $getTimestamp['timestamp'];
    }

    /**
     * {@inheritdoc}
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        $deletes = [];
        foreach ($paths as $path) {
            $deletes[] = $this->driver->delete($path);
        }

        if (in_array('false', $deletes, true)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function files(string $directory)
    {
        return $this->getContents($directory, 'file');
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles(string $directory, bool $showHiddenFiles = false)
    {
        return $this->getContents($directory, 'file', true);
    }

    /**
     * {@inheritdoc}
     */
    public function directories(string $directory)
    {
        $contents = $this->driver->listContents($directory, false);

        return $this->getContents($directory, 'dir');
    }

    /**
     * {@inheritdoc}
     */
    public function allDirectories(string $directory)
    {
        return $this->getContents($directory, 'dir', true);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(string $path, array $config = [])
    {
        return $this->driver->createDir($path, new FlyConfig($config));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $directory)
    {
        return $this->driver->deleteDir($directory);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanDirectory(string $dirname)
    {
        if (!$this->isDirectory($dirname)) {
            return false;
        }

        $directories = $this->allDirectories($dirname);

        foreach ($directories as $dirname) {
            @rmdir($dirname);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory(string $dirname)
    {
        return $this->driver->getMetadata($dirname)['type'] === 'dir';
    }

    /**
     * Get the Flysystem driver.
     *
     * @return object
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Call a Flysystem driver plugin.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return call_user_func_array([$this->driver, $method], $arguments);
    }

    /**
     * Filter directory contents by type.
     *
     * @param array  $contents
     * @param string $type
     *
     * @return array
     */
    private function filterContentsByType(array $contents, string $type): array
    {
        $return   = [];

        foreach ($contents as $key => $value) {
            if (Arr::get($contents, $key) === $value) {
                if (isset($value['path'])) {
                    $return[$key] = $value['path'];
                }
            }
        }

        return array_values($return);
    }

    /**
     * Parse the given visibility value.
     *
     * @param string|null $visibility
     *
     * @throws \InvalidArgumentException
     *
     * @return null|string
     */
    private function parseVisibility(string $visibility = null)
    {
        if ($visibility === null) {
            return;
        }

        switch ($visibility) {
            case FilesystemContract::VISIBILITY_PUBLIC:
                return AdapterInterface::VISIBILITY_PUBLIC;

            case FilesystemContract::VISIBILITY_PRIVATE:
                return AdapterInterface::VISIBILITY_PRIVATE;
        }

        throw new InvalidArgumentException('Unknown visibility: ' . $visibility);
    }

    /**
     * Get content from a dir.
     *
     * @param string $directory
     * @param string $typ
     * @param bool   $recursive
     *
     * @return array
     */
    private function getContents(string $directory, string $typ, bool $recursive = false): array
    {
        $contents = $this->driver->listContents($directory, $recursive);

        return $this->filterContentsByType($contents, $typ);
    }
}
