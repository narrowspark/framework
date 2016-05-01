<?php
namespace Viserio\Filesystem;

use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config as FlyConfig;
use League\Flysystem\FileNotFoundException as FlyFileNotFoundException;
use Viserio\Contracts\Filesystem\Directorysystem as DirectorysystemContract;
use Viserio\Contracts\Filesystem\FileNotFoundException;
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
        $visibility = isset($configs['visibility']) ? $configs['visibility'] : null;

        $configs['visibility'] = $this->parseVisibility($visibility);

        if (is_resource($contents)) {
            return $this->driver->writeStream($path, $contents, $configs);
        }

        return $this->driver->write($path, $contents, $configs);
    }

    /**
     * {@inheritdoc}
     */
    public function update($path, $contents, array $config = [])
    {
        return $this->upload($path, $contents, $config);
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
        return $this->driver->getMimetype($path);
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
    public function directories($directory)
    {
        $contents = $this->driver->listContents($directory, false);

        return $this->filterContentsByType($contents, 'dir');
    }

    /**
     * {@inheritdoc}
     */
    public function allDirectories($directory)
    {
        $contents = $this->driver->listContents($directory, true);

        return $this->filterContentsByType($contents, 'dir');
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory($path, array $config = [])
    {
        $flyConfig = new FlyConfig($config);

        return $this->driver->createDir($path, $flyConfig);
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
        return $this->dirver->getMetadata($dirname)['type'] === 'dir';
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
