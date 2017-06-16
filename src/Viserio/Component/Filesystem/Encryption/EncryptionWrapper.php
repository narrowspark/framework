<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Encryption;

use Defuse\Crypto\File;
use Defuse\Crypto\Key;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;

class EncryptionWrapper
{
    /**
     * Encryption key.
     *
     * @var \Defuse\Crypto\Key
     */
    protected $key;

    /**
     * Filesystem instance.
     *
     * @var \Viserio\Component\Contracts\Filesystem\Filesystem
     */
    protected $adapter;

    /**
     * Create a new encryption wrapper instance.
     *
     * @param \Viserio\Component\Contracts\Filesystem\Filesystem $adapter
     * @param \Defuse\Crypto\Key                                 $key
     */
    public function __construct(FilesystemContract $adapter, Key $key)
    {
        $this->adapter = $adapter;
        $this->key     = $key;
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
        return call_user_func_array([$this->adapter, $method], $arguments);
    }

    /**
     * Read a file.
     *
     * @param string $path the path to the file
     *
     * @throws \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
     *
     * @return string|false the file contents or false on failure
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
     * @throws \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
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
     * @param string|resource $contents
     * @param array           $config   an optional configuration array
     *
     * @return bool
     */
    public function put(string $path, $contents, array $config = []): bool
    {
        if (is_resource($contents)) {
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
     * @throws \Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException
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

    /**
     * Returns a stream representation of a string.
     *
     * @param string $contents The string
     *
     * @return resource
     */
    private function getStreamFromString(string $contents)
    {
        $resource = fopen('php://memory', 'r+b');

        File::writeBytes($resource, $contents);

        rewind($resource);

        return $resource;
    }

    /**
     * Decrypts a stream.
     *
     * @param resource $resource the stream to decrypt
     *
     * @return resource
     */
    private function decryptStream($resource)
    {
        $out = fopen('php://memory', 'r+b');

        if ($resource != false) {
            File::decryptResource($resource, $out, $this->key);
        } else {
            $out = '';
        }

        rewind($out);

        return $out;
    }

    /**
     * Encrypts a stream.
     *
     * @param resource $resource the stream to encrypt
     *
     * @return resource
     */
    private function encryptStream($resource)
    {
        $out = fopen('php://temp', 'r+b');

        File::encryptResource($resource, $out, $this->key);

        rewind($out);

        return $out;
    }

    /**
     * Decrypts a string.
     *
     * @param string $contents the string to decrypt
     *
     * @return string
     */
    private function decryptString(string $contents): string
    {
        $resource = $this->getStreamFromString($contents);

        return (string) stream_get_contents($this->decryptStream($resource));
    }

    /**
     * Encrypts a string.
     *
     * @param string $contents the string to encrypt
     *
     * @return string
     */
    private function encryptString(string $contents): string
    {
        $resource = $this->getStreamFromString($contents);

        return (string) stream_get_contents($this->encryptStream($resource));
    }
}
