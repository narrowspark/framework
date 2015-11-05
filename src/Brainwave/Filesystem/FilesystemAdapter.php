<?php
namespace Brainwave\Filesystem;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */
use Brainwave\Contracts\Filesystem\FileNotFoundException as ContractFileNotFoundException;
use Brainwave\Contracts\Filesystem\Filesystem as CloudFilesystemContract;
use Brainwave\Support\Collection;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config as FlyConfig;
use League\Flysystem\FileNotFoundException;

/**
 * FilesystemAdapter.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3-dev
 */
class FilesystemAdapter implements CloudFilesystemContract
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
     * Determine if a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function exists($path)
    {
        return $this->driver->has($path);
    }

    /**
     * Get the contents of a file.
     *
     * @param string $path
     *
     * @throws \Brainwave\Contracts\Filesystem\FileNotFoundException;
     *
     * @return array|false
     */
    public function get($path)
    {
        try {
            return $this->driver->read($path);
        } catch (FileNotFoundException $exception) {
            throw new ContractFileNotFoundException($path, $exception->getCode(), $exception);
        }
    }

    /**
     * Write the contents of a file.
     *
     * @param string $path
     * @param string $contents
     * @param array  $configs
     *
     * @return bool
     */
    public function put($path, $contents, array $configs = [])
    {
        $configs['visibility' => $this->parseVisibility(isset($configs['visibility']) ?: null)];

        if (is_resource($contents)) {
            return $this->driver->putStream($path, $contents, $configs);
        }

        return $this->driver->put($path, $contents, $configs);
    }

    /**
     * Get the visibility for the given path.
     *
     * @param string $path
     *
     * @return string
     */
    public function getVisibility($path)
    {
        if ($this->driver->getVisibility($path) === AdapterInterface::VISIBILITY_PUBLIC) {
            return CloudFilesystemContract::VISIBILITY_PUBLIC;
        }

        return CloudFilesystemContract::VISIBILITY_PRIVATE;
    }

    /**
     * Set the visibility for the given path.
     *
     * @param string $path
     * @param string $visibility
     */
    public function setVisibility($path, $visibility)
    {
        $this->driver->setVisibility($path, $this->parseVisibility($visibility));
    }

    /**
     * Prepend to a file.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function prepend($path, $data)
    {
        if ($this->exists($path)) {
            return $this->put($path, $data.PHP_EOL.$this->get($path));
        }

        return $this->put($path, $data);
    }

    /**
     * Append to a file.
     *
     * @param string $path
     * @param string $data
     *
     * @return bool
     */
    public function append($path, $data)
    {
         if ($this->exists($path)) {
             return $this->put($path, $this->get($path).PHP_EOL.$data);
         }

         return $this->put($path, $data);
    }

    /**
     * Delete the file at a given path.
     *
     * @param string|array $paths
     *
     * @return bool
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
     * Copy a file to a new location.
     *
     * @param string $from
     * @param string $to
     *
     * @return bool
     */
    public function copy($from, $to)
    {
        return $this->driver->copy($from, $to);
    }

    /**
     * Move a file to a new location.
     *
     * @param string $from
     * @param string $to
     *
     * @return bool|null
     */
    public function move($from, $to)
    {
        $this->driver->rename($from, $to);
    }

    /**
     * Get the file size of a given file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function size($path)
    {
        return $this->driver->getSize($path);
    }

    /**
     * Get the file's last modification time.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function lastModified($path)
    {
        return $this->driver->getTimestamp($path);
    }

    /**
     * Get an array of all files in a directory.
     *
     * @param string|null $directory
     * @param bool        $recursive
     *
     * @return array
     */
    public function files($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);

        return $this->filterContentsByType($contents, 'file');
    }

    /**
     * Get all of the files from the given directory (recursive).
     *
     * @param string|null $directory
     *
     * @return array
     */
    public function allFiles($directory = null)
    {
        return $this->files($directory, true);
    }

    /**
     * Get all of the directories within a given directory.
     *
     * @param string|null $directory
     * @param bool        $recursive
     *
     * @return array
     */
    public function directories($directory = null, $recursive = false)
    {
        $contents = $this->driver->listContents($directory, $recursive);

        return $this->filterContentsByType($contents, 'dir');
    }

    /**
     * Get all (recursive) of the directories within a given directory.
     *
     * @param string|null $directory
     *
     * @return array
     */
    public function allDirectories($directory = null)
    {
        return $this->directories($directory, true);
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
     * Filter directory contents by type.
     *
     * @param array  $contents
     * @param string $type
     *
     * @return array
     */
    protected function filterContentsByType($contents, $type)
    {
        return Collection::make($contents)
           ->where('type', $type)
           ->pluck('path')
           ->values()
           ->all();
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
     * @param  string  $method
     * @param  array  $arguments
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, array $arguments)
    {
        return $this->driver->__call($method, $arguments);
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
            case CloudFilesystemContract::VISIBILITY_PUBLIC:
                return AdapterInterface::VISIBILITY_PUBLIC;

            case CloudFilesystemContract::VISIBILITY_PRIVATE:
                return AdapterInterface::VISIBILITY_PRIVATE;
        }

        throw new \InvalidArgumentException('Unknown visibility: '.$visibility);
    }
}
