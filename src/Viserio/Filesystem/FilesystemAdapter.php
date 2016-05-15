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
    public function has($path)
    {
        return $this->driver->has($path);
    }

    /**
     * {@inheritdoc}
     */
    public function read($path)
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
    public function write($path, $contents, array $config = [])
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
    public function update($path, $contents, array $config = [])
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
    public function getVisibility($path)
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
    public function setVisibility($path, $visibility)
    {
        $this->driver->setVisibility($path, $this->parseVisibility($visibility));
    }

    /**
     * {@inheritdoc}
     */
    public function copy($originFile, $targetFile, $override = false)
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
    public function move($from, $to)
    {
        $this->driver->rename($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize($path)
    {
        $size = $this->driver->getSize($path);

        return !$size ?: $size['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
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
    public function getTimestamp($path)
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
    public function files($directory)
    {
        return $this->getContents($directory, 'file');
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles($directory)
    {
        return $this->getContents($directory, 'file', true);
    }

    /**
     * {@inheritdoc}
     */
    public function directories($directory)
    {
        $contents = $this->driver->listContents($directory, false);

        return $this->getContents($directory, 'dir');
    }

    /**
     * {@inheritdoc}
     */
    public function allDirectories($directory)
    {
        return $this->getContents($directory, 'dir', true);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory($path, array $config = [])
    {
        return $this->driver->createDir($path, new FlyConfig($config));
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory($directory)
    {
        return $this->driver->deleteDir($directory);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanDirectory($dirname)
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
    public function isDirectory($dirname)
    {
        return $this->driver->getMetadata($dirname)['type'] === 'dir';
    }

    /**
     * Get the Flysystem driver.
     *
     * @return AdapterInterface
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
    public function __call($method, array $arguments)
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
    private function filterContentsByType($contents, $type)
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
    private function parseVisibility($visibility)
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
    private function getContents($directory, $typ, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);

        return $this->filterContentsByType($contents, $typ);
    }
}
