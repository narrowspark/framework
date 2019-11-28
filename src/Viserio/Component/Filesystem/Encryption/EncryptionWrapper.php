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

use ParagonIE\Halite\Alerts\FileAccessDenied;
use ParagonIE\Halite\Alerts\FileModified;
use ParagonIE\Halite\File;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use SodiumException;
use Viserio\Contract\Filesystem\Exception\FileAccessDeniedException;
use Viserio\Contract\Filesystem\Exception\FileModifiedException;
use Viserio\Contract\Filesystem\Exception\FilesystemException;
use Viserio\Contract\Filesystem\Exception\RuntimeException;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;

class EncryptionWrapper implements FilesystemContract
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
     * Read a file.
     *
     * @param string $path the path to the file
     *
     * @throws SodiumException
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     *
     * @return false|string the file contents or false on failure
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
     * @throws SodiumException
     * @throws \ParagonIE\Halite\Alerts\CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\FileError
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
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
     * @throws \Viserio\Contract\Filesystem\Exception\FileModifiedException
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
     * @throws SodiumException
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
     * @throws SodiumException
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
     * @throws \Viserio\Contract\Filesystem\Exception\FilesystemException
     *
     * @return resource
     */
    private function decryptStream($resource)
    {
        \error_clear_last();
        $stream = \fopen('php://memory', 'r+b');

        if ($stream === false) {
            throw FilesystemException::createFromPhpError();
        }

        if ($resource !== false) {
            try {
                File::decrypt($resource, $stream, $this->key);
            } catch (FileAccessDenied $exception) {
                throw new FileAccessDeniedException($exception->getMessage(), $exception->getCode(), $exception);
            } catch (FileModified $exception) {
                throw new FileModifiedException($exception->getMessage(), $exception->getCode(), $exception);
            }

            \rewind($stream);
        }

        return $stream;
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
     * @throws \Viserio\Contract\Filesystem\Exception\FilesystemException
     *
     * @return resource
     */
    private function encryptStream($resource)
    {
        \error_clear_last();
        $stream = \fopen('php://temp', 'w+b');

        if ($stream === false) {
            throw FilesystemException::createFromPhpError();
        }

        if ($resource !== false) {
            try {
                File::encrypt($resource, $stream, $this->key);
            } catch (FileAccessDenied $exception) {
                throw new FileAccessDeniedException($exception->getMessage(), $exception->getCode(), $exception);
            } catch (FileModified $exception) {
                throw new FileModifiedException($exception->getMessage(), $exception->getCode(), $exception);
            }

            \rewind($stream);
        }

        return $stream;
    }

    /**
     * Decrypts a string.
     *
     * @param string $contents the string to decrypt
     *
     * @throws SodiumException
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
     * @throws SodiumException
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
     * @throws SodiumException
     * @throws \Viserio\Contract\Filesystem\Exception\FileNotFoundException
     * @throws \Viserio\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Contract\Filesystem\Exception\RuntimeException
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

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function directories(string $directory): array
    {
        return $this->adapter->directories($directory);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function allDirectories(string $directory): array
    {
        return $this->adapter->allDirectories($directory);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createDirectory(string $dirname, array $config = []): bool
    {
        return $this->adapter->createDirectory($dirname, $config);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function deleteDirectory(string $dirname): bool
    {
        return $this->adapter->deleteDirectory($dirname);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function cleanDirectory(string $dirname): bool
    {
        return $this->adapter->cleanDirectory($dirname);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function isDirectory(string $dirname): bool
    {
        return $this->adapter->isDirectory($dirname);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function copyDirectory(string $directory, string $destination, array $options = []): bool
    {
        return $this->adapter->copyDirectory($directory, $destination, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function moveDirectory(string $directory, string $destination, array $options = []): bool
    {
        return $this->adapter->moveDirectory($directory, $destination, $options);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function has(string $path): bool
    {
        return $this->adapter->has($path);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function append(string $path, string $contents, array $config = []): bool
    {
        return $this->adapter->append($path, $contents, $config);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function appendStream(string $path, $resource, array $config = []): bool
    {
        return $this->adapter->appendStream($path, $resource, $config);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getVisibility(string $path): string
    {
        return $this->adapter->getVisibility($path);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function setVisibility(string $path, string $visibility): bool
    {
        return $this->adapter->setVisibility($path, $visibility);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function copy($originFile, $targetFile, $override = false): bool
    {
        return $this->adapter->copy($originFile, $targetFile, $override);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function move(string $from, string $to): bool
    {
        return $this->adapter->move($from, $to);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getSize(string $path)
    {
        return $this->adapter->getSize($path);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getMimetype(string $path)
    {
        return $this->adapter->getMimetype($path);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getTimestamp(string $path)
    {
        return $this->adapter->getTimestamp($path);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function url(string $path): string
    {
        return $this->adapter->url($path);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function delete($paths): bool
    {
        return $this->adapter->delete($paths);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function files(string $directory): array
    {
        return $this->adapter->files($directory);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function allFiles(string $directory, bool $showHiddenFiles = false): array
    {
        return $this->adapter->allFiles($directory, $showHiddenFiles);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getExtension(string $path): string
    {
        return $this->adapter->getExtension($path);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function withoutExtension(string $path, ?string $extension = null): string
    {
        return $this->adapter->withoutExtension($path, $extension);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function changeExtension(string $path, string $extension): string
    {
        return $this->adapter->changeExtension($path, $extension);
    }
}
