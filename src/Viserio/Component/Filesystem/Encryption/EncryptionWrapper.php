<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem\Encryption;

use BadMethodCallException;
use ParagonIE\Halite\Alerts\FileAccessDenied;
use ParagonIE\Halite\Alerts\FileModified;
use ParagonIE\Halite\File;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use Viserio\Contract\Filesystem\Exception\FileAccessDeniedException;
use Viserio\Contract\Filesystem\Exception\FileModifiedException;
use Viserio\Contract\Filesystem\Exception\RuntimeException;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;

class EncryptionWrapper
{
    /**
     * Filesystem instance.
     *
     * @var \Viserio\Contract\Filesystem\Filesystem
     */
    protected $adapter;

    /**
     * Encryption key instance.
     *
     * @var \ParagonIE\Halite\Symmetric\EncryptionKey
     */
    private $key;

    /**
     * Create a new encryption wrapper instance.
     *
     * @param \Viserio\Contract\Filesystem\Filesystem   $adapter
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key
     */
    public function __construct(FilesystemContract $adapter, EncryptionKey $key)
    {
        $this->adapter = $adapter;
        $this->key = $key;
    }

    /**
     * Hide this from var_dump(), etc.
     *
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'key' => 'private',
        ];
    }

    /**
     * Calls adapter functions.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @throws BadMethodCallException
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function __call(string $method, array $arguments)
    {
        return $this->adapter->{$method}(...$arguments);
    }

    /**
     * Read a file.
     *
     * @param string $path the path to the file
     *
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
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
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
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
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
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
     * @throws FileModifiedException
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
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
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
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
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
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
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     *
     * @return bool
     */
    public function updateStream(string $path, $resource, array $config = []): bool
    {
        $resource = $this->encryptStream($resource);

        return $this->adapter->updateStream($path, $resource, $config);
    }

    /**
     * Decrypts a stream.
     *
     * @param false|resource $resource the stream to decrypt
     *
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     *
     * @return resource
     */
    private function decryptStream($resource)
    {
        $out = \fopen('php://memory', 'r+b');

        if ($resource !== false) {
            try {
                File::decrypt($resource, $out, $this->key);
            } catch (FileAccessDenied $exception) {
                throw new FileAccessDeniedException($exception->getMessage(), $exception->getCode(), $exception);
            } catch (FileModified $exception) {
                throw new FileModifiedException($exception->getMessage(), $exception->getCode(), $exception);
            }

            \rewind($out);
        }

        return $out;
    }

    /**
     * Encrypts a stream.
     *
     * @param false|resource $resource the stream to encrypt
     *
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     *
     * @return resource
     */
    private function encryptStream($resource)
    {
        $out = \fopen('php://temp', 'w+b');

        if ($resource !== false) {
            try {
                File::encrypt($resource, $out, $this->key);
            } catch (FileAccessDenied $exception) {
                throw new FileAccessDeniedException($exception->getMessage(), $exception->getCode(), $exception);
            } catch (FileModified $exception) {
                throw new FileModifiedException($exception->getMessage(), $exception->getCode(), $exception);
            }

            \rewind($out);
        }

        return $out;
    }

    /**
     * Decrypts a string.
     *
     * @param string $contents the string to decrypt
     *
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return string
     */
    private function decryptString(string $contents): string
    {
        $resource = $this->getStreamFromString($contents);

        return (string) \stream_get_contents($this->decryptStream($resource));
    }

    /**
     * Encrypts a string.
     *
     * @param string $contents the string to encrypt
     *
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     *
     * @return string
     */
    private function encryptString(string $contents): string
    {
        $resource = $this->getStreamFromString($contents);

        return (string) \stream_get_contents($this->encryptStream($resource));
    }

    /**
     * Returns a stream representation of a string.
     *
     * @param string $contents The string
     *
     * @throws \Viserio\Contract\Filesystem\Exception\RuntimeException
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     *
     * @return resource
     */
    private function getStreamFromString(string $contents)
    {
        $path = \bin2hex(\random_bytes(16));

        $this->adapter->write($path, $contents);

        \sodium_memzero($contents);

        $streamContent = $this->adapter->readStream($path);

        $this->adapter->delete($path);

        if ($streamContent !== false) {
            return $streamContent;
        }

        throw new RuntimeException('Created file for string content cant be read.');
    }
}
