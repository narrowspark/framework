<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Encryption;

use Viserio\Component\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Encryption\Key;

class EncryptionWrapper
{
    /**
     * Encryption key.
     *
     * @var \Viserio\Component\Filesystem\Encryption\File
     */
    protected $file;

    /**
     * Filesystem instance.
     *
     * @var \Viserio\Component\Contract\Filesystem\Filesystem
     */
    protected $adapter;

    /**
     * Create a new encryption wrapper instance.
     *
     * @param \Viserio\Component\Contract\Filesystem\Filesystem $adapter
     * @param \Viserio\Component\Encryption\Key                 $key
     */
    public function __construct(FilesystemContract $adapter, Key $key)
    {
        $this->adapter = $adapter;
        $this->file    = new File($key);
    }

    /**
     * Calls adapter functions.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function __call(string $method, array $arguments)
    {
        return \call_user_func_array([$this->adapter, $method], $arguments);
    }

    /**
     * Read a file.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return bool|string the file contents or false on failure
     */
    public function read(string $path)
    {
        if (($result = $this->adapter->read($path)) === false) {
            return false;
        }

        return $this->decryptString($result);
    }

    /**
     * Retrieves a read-stream for a path.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return resource
     */
    public function readStream(string $path)
    {
        return $this->decryptStream($this->adapter->readStream($path));
    }

    /**
     * Write a new file.
     *
     * @param string $path     the path of the new file
     * @param string $contents the file contents
     * @param array  $config   an optional configuration array
     *
     * @return bool true on success, false on failure
     */
    public function write(string $path, $contents, array $config = []): bool
    {
        $contents = $this->encryptString($contents);

        return $this->adapter->write($path, $contents, $config);
    }

    /**
     * Write a new file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param array    $config   an optional configuration array
     *
     * @return bool
     */
    public function writeStream(string $path, $resource, array $config = []): bool
    {
        $resource = $this->encryptStream($resource);

        return $this->adapter->writeStream($path, $resource, $config);
    }

    /**
     * Write the contents of a file.
     *
     * @param string          $path
     * @param resource|string $contents
     * @param array           $config   an optional configuration array
     *
     * @return bool
     */
    public function put(string $path, $contents, array $config = []): bool
    {
        if (\is_resource($contents)) {
            $contents = $this->encryptStream($contents);
        } else {
            $contents = $this->encryptString($contents);
        }

        return $this->adapter->put($path, $contents, $config);
    }

    /**
     * Update an existing file.
     *
     * @param string $path     the path of the existing file
     * @param string $contents the file contents
     * @param array  $config   an optional configuration array
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return bool true on success, false on failure
     */
    public function update(string $path, string $contents, array $config = []): bool
    {
        $contents = $this->encryptString($contents);

        return $this->adapter->update($path, $contents, $config);
    }

    /**
     * Update a file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param array    $config
     *
     * @return bool
     */
    public function updateStream(string $path, $resource, array $config = []): bool
    {
        $resource = $this->encryptStream($resource);

        return $this->adapter->updateStream($path, $resource, $config);
    }
}
