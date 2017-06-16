<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem;

use InvalidArgumentException;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Config as FlyConfig;
use Narrowspark\Arr\Arr;
use RuntimeException;
use Viserio\Component\Contracts\Filesystem\Directorysystem as DirectorysystemContract;
use Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException;
use Viserio\Component\Contracts\Filesystem\Exception\IOException as ViserioIOException;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Filesystem\Traits\FilesystemExtensionTrait;
use Viserio\Component\Filesystem\Traits\FilesystemHelperTrait;
use Viserio\Component\Support\Traits\MacroableTrait;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class FilesystemAdapter implements FilesystemContract, DirectorysystemContract
{
    use NormalizePathAndDirectorySeparatorTrait;
    use FilesystemExtensionTrait;
    use FilesystemHelperTrait;
    use MacroableTrait;

    /**
     * The Flysystem filesystem implementation.
     *
     * @var \League\Flysystem\AdapterInterface
     */
    protected $driver;

    /**
     * Adapter configs.
     *
     * @var array
     */
    protected $config;

    /**
     * Create a new filesystem adapter instance.
     *
     * @param \League\Flysystem\AdapterInterface $driver
     * @param array                              $config
     */
    public function __construct(AdapterInterface $driver, array $config)
    {
        $this->driver = $driver;
        $this->config = $config;
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
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return bool
     */
    public function has(string $path): bool
    {
        $has = $this->driver->has($path);

        if ($has === null) {
            return false;
        } elseif (is_array($has)) {
            return $has['path'] !== '';
        }

        return $has;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $path)
    {
        if (! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $content = $this->driver->read($path);

        if ($content !== false) {
            return $content['contents'];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function readStream(string $path)
    {
        if (! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $content = $this->driver->readStream($path);

        if ($content !== false) {
            return $content['stream'];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $path, string $contents, array $config = []): bool
    {
        $config['visibility'] = $this->parseVisibility($config['visibility'] ?? null) ?: [];

        $flyConfig = new FlyConfig($config);

        return $this->driver->write($path, $contents, $flyConfig) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function writeStream(string $path, $resource, array $config = []): bool
    {
        $config['visibility'] = $this->parseVisibility($config['visibility'] ?? null) ?: [];

        $flyConfig = new FlyConfig($config);

        return $this->driver->writeStream($path, $resource, $flyConfig) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $path, $contents, array $config = []): bool
    {
        $config['visibility'] = $this->parseVisibility($config['visibility'] ?? null) ?: [];

        $flyConfig = new FlyConfig($config);

        if (is_resource($contents)) {
            if ($this->has($path)) {
                return $this->driver->updateStream($path, $contents, $flyConfig) !== false;
            }

            return $this->driver->writeStream($path, $contents, $flyConfig) !== false;
        }

        if ($this->has($path)) {
            return $this->driver->update($path, $contents, $flyConfig) !== false;
        }

        return $this->driver->write($path, $contents, $flyConfig) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function append(string $path, string $contents, array $config = []): bool
    {
        if ($this->has($path)) {
            $contents = $this->read($path) . $contents;

            if (! $this->driver->delete($path)) {
                throw new RuntimeException(sprintf('File [%s] cannot be deleted.', $path));
            }
        }

        return $this->write($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function appendStream(string $path, $resource, array $config = []): bool
    {
        if ($this->has($path)) {
            return $this->updateStream($path, $resource, $config);
        }

        return $this->writeStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $path, string $contents, array $config = []): bool
    {
        if (! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $flyConfig = new FlyConfig($config);

        return $this->driver->update($path, $contents, $flyConfig) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function updateStream(string $path, $resource, array $config = []): bool
    {
        $config['visibility'] = $this->parseVisibility($config['visibility'] ?? null) ?: [];

        $flyConfig = new FlyConfig($config);

        return $this->driver->updateStream($path, $resource, $flyConfig) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibility(string $path): string
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
    public function setVisibility(string $path, string $visibility): bool
    {
        return $this->driver->setVisibility(
            $path,
            $this->parseVisibility($visibility)
        ) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function copy($originFile, $targetFile, $override = false)
    {
        if (! $this->has($originFile)) {
            throw new FileNotFoundException($originFile);
        }

        if (method_exists($this->driver, 'applyPathPrefix')) {
            $orginal = $this->driver->applyPathPrefix($originFile);
            $target  = $this->driver->applyPathPrefix($targetFile);
        } else {
            $orginal = $originFile;
            $target  = $targetFile;
        }

        // https://bugs.php.net/bug.php?id=64634
        if (@fopen($orginal, 'r') === false) {
            throw new ViserioIOException(sprintf(
                'Failed to copy [%s] to [%s] because source file could not be opened for reading.',
                $orginal,
                $target
            ), 0, null, $orginal);
        }

        // Stream context created to allow files overwrite when using FTP stream wrapper - disabled by default
        if (@fopen($target, 'w', false, stream_context_create(['ftp' => ['overwrite' => true]])) === false) {
            throw new ViserioIOException(sprintf(
                'Failed to copy [%s] to [%s] because target file could not be opened for writing.',
                $orginal,
                $target
            ), 0, null, $orginal);
        }

        $this->driver->copy($originFile, $targetFile);

        if (! is_file($target)) {
            throw new ViserioIOException(sprintf(
                'Failed to copy [%s] to [%s].',
                $originFile,
                $target
            ), 0, null, $originFile);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function move(string $from, string $to): bool
    {
        return $this->driver->rename($from, $to);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(string $path)
    {
        $size = $this->driver->getSize($path);

        if ($size !== false) {
            return $size['size'];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimetype(string $path)
    {
        if (! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $mimetype = $this->driver->getMimetype($path);

        if ($mimetype !== false) {
            return $mimetype['mimetype'];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp(string $path)
    {
        if (! $this->has($path)) {
            throw new FileNotFoundException($path);
        }

        $getTimestamp = $this->driver->getTimestamp($path);

        if ($getTimestamp !== false) {
            return $getTimestamp['timestamp'];
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    public function url(string $path): string
    {
        $adapter = $this->driver;

        if ($adapter instanceof AwsS3Adapter) {
            $path = $adapter->getPathPrefix() . $path;

            return $adapter->getClient()->getObjectUrl($adapter->getBucket(), $path);
        } elseif ($adapter instanceof LocalAdapter) {
            if (isset($this->config['url'])) {
                return self::normalizeDirectorySeparator(
                    $this->config['url'] . '/' . $path
                );
            }

            return $adapter->getPathPrefix() . $path;
        } elseif (method_exists($adapter, 'getUrl')) {
            return $adapter->getUrl($path);
        }

        throw new RuntimeException('This driver does not support retrieving URLs.');
    }

    /**
     * {@inheritdoc}
     */
    public function delete(array $paths): bool
    {
        $success = true;

        foreach ($paths as $path) {
            try {
                if (! $this->driver->delete($path)) {
                    $success = false;
                }
            } catch (FileNotFoundException $e) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * {@inheritdoc}
     */
    public function files(string $directory): array
    {
        return $this->getContents($directory, 'file');
    }

    /**
     * {@inheritdoc}
     */
    public function allFiles(string $directory, bool $showHiddenFiles = false): array
    {
        return $this->getContents($directory, 'file', true);
    }

    /**
     * {@inheritdoc}
     */
    public function directories(string $directory): array
    {
        return $this->getContents($directory, 'dir');
    }

    /**
     * {@inheritdoc}
     */
    public function allDirectories(string $directory): array
    {
        return $this->getContents($directory, 'dir', true);
    }

    /**
     * {@inheritdoc}
     */
    public function createDirectory(string $path, array $config = []): bool
    {
        return $this->driver->createDir($path, new FlyConfig($config)) !== false;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteDirectory(string $directory): bool
    {
        return $this->driver->deleteDir($directory);
    }

    /**
     * {@inheritdoc}
     */
    public function cleanDirectory(string $dirname): bool
    {
        if (! $this->isDirectory($dirname)) {
            return false;
        }

        $directories = $this->allDirectories($dirname);

        foreach ($directories as $dirname) {
            @rmdir($this->getNormalizedOrPrefixedPath($dirname));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isDirectory(string $dirname): bool
    {
        return is_dir($this->getNormalizedOrPrefixedPath($dirname));
    }

    /**
     * {@inheritdoc}
     */
    public function copyDirectory(string $directory, string $destination, array $options = []): bool
    {
        if (! $this->isDirectory($directory)) {
            return false;
        }

        if (! is_dir($destination)) {
            $this->createDirectory($destination, ['visibility' => 'public']);
        }

        $recursive = $options['recursive'] ?? true;

        $contents = $this->driver->listContents($directory, $recursive);

        foreach ($contents as $item) {
            if ($item['type'] == 'dir') {
                $this->createDirectory(
                    $destination . str_replace($directory, '', $item['path']),
                    ['visibility' => $this->getVisibility($item['path'])]
                );
            }

            if ($item['type'] == 'file') {
                $this->copy(
                    $item['path'],
                    $destination . str_replace($directory, '', $item['path'])
                );
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function moveDirectory(string $directory, string $destination, array $options = []): bool
    {
        $overwrite = $options['overwrite'] ?? false;

        if ($overwrite && $this->isDirectory($destination)) {
            if (! $this->deleteDirectory($destination)) {
                return false;
            }
        }

        $copy = $this->copyDirectory(
            $directory,
            $destination,
            ['visibility' => $this->getVisibility($directory)]
        );
        $delete = $this->deleteDirectory($directory);

        return ! (! $copy && ! $delete);
    }

    /**
     * Get the Flysystem driver.
     *
     * @return \League\Flysystem\AdapterInterface
     */
    public function getDriver()
    {
        return $this->driver;
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
        if (isset($this->driver)) {
            $prefix = method_exists($this->driver, 'getPathPrefix') ? $this->driver->getPathPrefix() : '';

            $path = $prefix . $path;
        }

        return self::normalizeDirectorySeparator($path);
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
        $return = [];

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
            return null;
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
