<?php
namespace Viserio\Filesystem;

use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config as FlyConfig;
use League\Flysystem\FileNotFoundException as FlyFileNotFoundException;
use RuntimeException;
use Viserio\Contracts\Filesystem\Directorysystem as DirectorysystemContract;
use Viserio\Contracts\Filesystem\FileNotFoundException;
use Viserio\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Support\Collection;

class FilesystemAdapter implements FilesystemContract, DirectorysystemContract
{
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
        try {
            return $this->driver->read($path);
        } catch (FlyFileNotFoundException $exception) {
            throw new FileNotFoundException($path, $exception->getCode(), $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function write($path, $contents, array $config = [])
    {
        $configs['visibility'] = $this->parseVisibility(isset($configs['visibility']) ?: null);

        if (is_resource($contents)) {
            return $this->driver->putStream($path, $contents, $configs);
        }

        return $this->driver->put($path, $contents, $configs);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, array $config = [])
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility($path)
    {
        if ($this->driver->getVisibility($path) === AdapterInterface::VISIBILITY_PUBLIC) {
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
        return $this->driver->copy($originFile, $targetFile);
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
        return $this->driver->getSize($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function extension($path)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp($path)
    {
        return $this->driver->getTimestamp($path);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($paths)
    {
        $paths = is_array($paths) ? $paths : func_get_args();

        foreach ($paths as $path) {
            $this->driver->delete($path);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function files($directory)
    {
        $contents = $this->driver->listContents($directory, false);

        return $this->filterContentsByType($contents, 'file');
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles($directory)
    {
        $contents = $this->driver->listContents($directory, true);

        return $this->filterContentsByType($contents, 'file');
    }

    /**
     * {@inheritdoc}
     */
    public function withoutExtension($path, $extension = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function changeExtension($path, $extension)
    {
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param string|null $directory
     * @param bool        $recursive
     *
     * @return array
     */
    public function directories($directory)
    {
        $contents = $this->driver->listContents($directory, false);

        return $this->filterContentsByType($contents, 'dir');
    }

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param string|null $directory
     *
     * @return array
     */
    public function allDirectories($directory)
    {
        $contents = $this->driver->listContents($directory, true);

        return $this->filterContentsByType($contents, 'dir');
    }

    /**
     * Create a directory.
     *
     * @param string    $path
     * @param FlyConfig $config
     *
     * @return array|false
     */
    public function makeDirectory($path, FlyConfig $config)
    {
        return $this->driver->createDir($path, $config);
    }

    /**
     * Recursively delete a directory.
     *
     * @param string $directory
     *
     * @return bool
     */
    public function deleteDirectory($directory)
    {
        return $this->driver->deleteDir($directory);
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
     * Get the URL for the file at the given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function url($path)
    {
        $driver = $this->driver->getAdapter();

        if (!$driver instanceof AwsS3Adapter) {
            throw new RuntimeException('This driver does not support retrieving URLs.');
        }

        $bucket = $driver->getBucket();

        return $driver->getClient()->getObjectUrl($bucket, $path);
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
        return $this->driver->__call($method, $arguments);
    }

    /**
     * Filter directory contents by type.
     *
     * @param array  $contents
     * @param string $type
     *
     * @return array
     */
    protected function filterContentsByType($contents, $type)
    {
        // return Collection::make($contents)
        //    ->where('type', $type)
        //    ->pluck('path')
        //    ->values()
        //    ->all();
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
    protected function parseVisibility($visibility)
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
}
