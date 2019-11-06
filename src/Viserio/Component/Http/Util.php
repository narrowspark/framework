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

namespace Viserio\Component\Http;

use Iterator;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Throwable;
use Viserio\Component\Http\Stream\PumpStream;
use Viserio\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Contract\Http\Exception\RuntimeException;
use function fopen;

/**
 * Some code in this class it taken from zend-diactoros.
 *
 * See the original here: https://github.com/zendframework/zend-diactoros/blob/master/src/ServerRequestFactory.php
 *
 * @copyright Copyright (c) 2015-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
final class Util
{
    public const UPPER_CASE = '_ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    public const LOWER_CASE = '-abcdefghijklmnopqrstuvwxyz';

    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }

    /**
     * Returns headers obtained from the SAPI (generally `$_SERVER`).
     *
     * @param int[]|string[] $server
     *
     * @return array
     */
    public static function getAllHeaders(array $server): array
    {
        $headers = [];

        foreach ($server as $key => $value) {
            if (\is_int($key)) {
                $headers[$key] = $value;

                continue;
            }

            if ($value === '') {
                continue;
            }

            // Apache prefixes environment variables with REDIRECT_
            // if they are added by rewrite rules
            if (\strpos($key, 'REDIRECT_') === 0) {
                $key = \substr($key, 9);
                // We will not overwrite existing variables with the
                // prefixed versions, though
                if (\array_key_exists($key, $server)) {
                    continue;
                }
            }

            if (\strpos($key, 'HTTP_') === 0) {
                $key = \substr($key, 5);

                if (! \array_key_exists($key, $_SERVER)) {
                    $name = \str_replace(' ', '-', \ucwords(\strtolower(\str_replace('_', ' ', $key))));
                    $headers[$name] = $value;
                }

                continue;
            }

            if (\strpos($key, 'CONTENT_') === 0) {
                $name = \str_replace(' ', '-', \ucwords(\strtolower(\str_replace('_', ' ', $key))));
                $headers[$name] = $value;
            }
        }

        if (! \array_key_exists('Authorization', $headers)) {
            if (\array_key_exists('REDIRECT_HTTP_AUTHORIZATION', $_SERVER)) {
                $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            } elseif (\array_key_exists('PHP_AUTH_USER', $_SERVER)) {
                $basic_pass = $_SERVER['PHP_AUTH_PW'] ?? '';
                $headers['Authorization'] = 'Basic ' . \base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
            } elseif (\array_key_exists('PHP_AUTH_DIGEST', $_SERVER)) {
                $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
            }
        }

        return $headers;
    }

    /**
     * Safely opens a PHP stream resource using a filename.
     *
     * When fopen fails, PHP normally raises a warning. This function adds an
     * error handler that checks for errors and throws an exception instead.
     *
     * @param string $filename File to open
     * @param string $mode     Mode used to open the file
     *
     * @throws \Viserio\Contract\Http\Exception\RuntimeException if the file cannot be opened
     *
     * @return false|resource
     */
    public static function tryFopen(string $filename, string $mode)
    {
        $ex = null;

        \set_error_handler(static function () use ($filename, $mode, &$ex): void {
            $ex = new RuntimeException(\sprintf(
                'Unable to open [%s] using mode %s: %s',
                $filename,
                $mode,
                \func_get_args()[1]
            ));
        });

        $handle = \fopen($filename, $mode);

        \restore_error_handler();

        if ($ex instanceof Throwable) {
            throw $ex;
        }

        return $handle;
    }

    /**
     * Create a new stream based on the input type.
     *
     * Options is an associative array that can contain the following keys:
     * - metadata: Array of custom metadata.
     * - size: Size of the stream.
     *
     * @param null|bool|callable|float|int|Iterator|resource|StreamInterface|string $resource Entity body data
     * @param array                                                                 $options  Additional options
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException if the $resource arg is not valid
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public static function createStreamFor($resource = '', array $options = []): StreamInterface
    {
        if (\is_scalar($resource)) {
            $stream = self::tryFopen('php://temp', 'r+');

            if ($resource !== '') {
                \fwrite($stream, (string) $resource);
                \fseek($stream, 0);
            }

            return new Stream($stream, $options);
        }

        $type = \gettype($resource);

        if ($type === 'resource') {
            return new Stream($resource, $options);
        }

        if ($type === 'object') {
            if ($resource instanceof StreamInterface) {
                return $resource;
            }

            if ($resource instanceof Iterator) {
                return new PumpStream(static function () use ($resource) {
                    if (! $resource->valid()) {
                        return false;
                    }

                    $result = $resource->current();
                    $resource->next();

                    return $result;
                }, $options);
            }

            if (\method_exists($resource, '__toString')) {
                return self::createStreamFor($resource->__toString(), $options);
            }
        }

        if ($type === 'NULL') {
            return new Stream(self::tryFopen('php://temp', 'r+'), $options);
        }

        if (\is_callable($resource)) {
            return new PumpStream($resource, $options);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . \gettype($resource));
    }

    /**
     * Copy the contents of a stream into a string until the given number of
     * bytes have been read.
     *
     * @param \Psr\Http\Message\StreamInterface $stream Stream to read
     * @param int                               $maxLen Maximum number of bytes to read. Pass -1
     *                                                  to read the entire stream.
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public static function copyToString(StreamInterface $stream, int $maxLen = -1): string
    {
        $buffer = '';

        if ($maxLen === -1) {
            while (! $stream->eof()) {
                $buf = $stream->read(1048576);
                // Using a loose equality here to match on '' and false.
                if (empty($buf)) {
                    break;
                }

                $buffer .= $buf;
            }

            return $buffer;
        }

        $len = 0;

        while (! $stream->eof() && $len < $maxLen) {
            $buf = $stream->read($maxLen - $len);
            // Using a loose equality here to match on '' and false.
            if (empty($buf)) {
                break;
            }

            $buffer .= $buf;
            $len = \strlen($buffer);
        }

        return $buffer;
    }

    /**
     * Copy the contents of a stream into another stream until the given number
     * of bytes have been read.
     *
     * @param \Psr\Http\Message\StreamInterface $source Stream to read from
     * @param \Psr\Http\Message\StreamInterface $dest   Stream to write to
     * @param int                               $maxLen Maximum number of bytes to read. Pass -1
     *                                                  to read the entire stream.
     *
     * @throws \RuntimeException
     */
    public static function copyToStream(StreamInterface $source, StreamInterface $dest, int $maxLen = -1): void
    {
        if ($maxLen === -1) {
            while (! $source->eof()) {
                if (! (bool) $dest->write($source->read(1048576))) {
                    break;
                }
            }

            return;
        }

        $bufferSize = 8192;

        if ($maxLen === -1) {
            while (! $source->eof()) {
                if (! (bool) $dest->write($source->read($bufferSize))) {
                    break;
                }
            }
        } else {
            $remaining = $maxLen;

            while ($remaining > 0 && ! $source->eof()) {
                $buf = $source->read(\min($bufferSize, $remaining));
                $len = \strlen($buf);

                if (! $len) {
                    break;
                }

                $remaining -= $len;
                $dest->write($buf);
            }
        }
    }

    /**
     * Read a line from the stream up to the maximum allowed buffer length.
     *
     * @param \Psr\Http\Message\StreamInterface $stream    Stream to read from
     * @param int                               $maxLength Maximum buffer length
     *
     * @return string
     */
    public static function readline(StreamInterface $stream, ?int $maxLength = null): string
    {
        $buffer = '';
        $size = 0;

        while (! $stream->eof()) {
            $byte = $stream->read(1);
            // Using a loose equality here to match on '' and false.
            if ($byte === '' || $byte === false || $byte === null) {
                return $buffer;
            }

            $buffer .= $byte;

            // Break when a new line is found or the max length - 1 is reached
            if ($byte === "\n" || ++$size === $maxLength - 1) {
                break;
            }
        }

        return $buffer;
    }

    /**
     * Return an UploadedFile instance array.
     *
     * @param array $files A array which respect $_FILES structure
     *
     * @throws \Viserio\Contract\Http\Exception\InvalidArgumentException for unrecognized values
     *
     * @return array
     */
    public static function normalizeFiles(array $files): array
    {
        /**
         * @param array[]|string[]      $tmpNameTree
         * @param array[]|int[]         $sizeTree
         * @param array[]|int[]         $errorTree
         * @param null|array[]|string[] $nameTree
         * @param null|array[]|string[] $typeTree
         *
         * @return array[]|\Psr\Http\Message\UploadedFileInterface[]
         */
        $recursiveNormalize = static function (
            array $tmpNameTree,
            array $sizeTree,
            array $errorTree,
            ?array $nameTree = null,
            ?array $typeTree = null
        ) use (&$recursiveNormalize) {
            $normalized = [];

            foreach ($tmpNameTree as $key => $value) {
                if (\is_array($value)) {
                    // Traverse
                    $normalized[$key] = $recursiveNormalize(
                        $tmpNameTree[$key],
                        $sizeTree[$key],
                        $errorTree[$key],
                        $nameTree[$key] ?? null,
                        $typeTree[$key] ?? null
                    );

                    continue;
                }

                $normalized[$key] = self::createUploadedFileFromSpec([
                    'tmp_name' => $tmpNameTree[$key],
                    'size' => $sizeTree[$key],
                    'error' => $errorTree[$key],
                    'name' => $nameTree[$key] ?? null,
                    'type' => $typeTree[$key] ?? null,
                ]);
            }

            return $normalized;
        };

        /**
         * Normalize an array of file specifications.
         *
         * Loops through all nested files (as determined by receiving an array to the
         * `tmp_name` key of a `$_FILES` specification) and returns a normalized array
         * of UploadedFile instances.
         *
         * This function normalizes a `$_FILES` array representing a nested set of
         * uploaded files as produced by the php-fpm SAPI, CGI SAPI, or mod_php
         * SAPI.
         *
         * @param array $files
         *
         * @return \Psr\Http\Message\UploadedFileInterface[]
         */
        $normalizeUploadedFileSpecification = static function (array $files = []) use (&$recursiveNormalize) {
            if (! \array_key_exists('tmp_name', $files) || ! \is_array($files['tmp_name'])
                || ! \array_key_exists('size', $files) || ! \is_array($files['size'])
                || ! \array_key_exists('error', $files) || ! \is_array($files['error'])
            ) {
                throw new InvalidArgumentException(\sprintf('$files provided to %s MUST contain each of the keys "tmp_name", "size", and "error", with each represented as an array; one or more were missing or non-array values.', __FUNCTION__));
            }

            return $recursiveNormalize(
                $files['tmp_name'],
                $files['size'],
                $files['error'],
                $files['name'] ?? null,
                $files['type'] ?? null
            );
        };

        $normalized = [];

        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;

                continue;
            }

            if (\is_array($value) && \array_key_exists('tmp_name', $value) && \is_array($value['tmp_name'])) {
                $normalized[$key] = $normalizeUploadedFileSpecification($value);

                continue;
            }

            if (\is_array($value) && \array_key_exists('tmp_name', $value)) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);

                continue;
            }

            if (\is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);

                continue;
            }

            throw new InvalidArgumentException('Invalid value in files specification.');
        }

        return $normalized;
    }

    /**
     * Create and return an UploadedFile instance from a $_FILES specification.
     *
     * If the specification represents an array of values, this method will
     * delegate to normalizeNestedFileSpec() and return that return value.
     *
     * @param array $value $_FILES struct
     *
     * @return array|UploadedFileInterface
     */
    private static function createUploadedFileFromSpec(array $value)
    {
        if (\is_array($value['tmp_name'])) {
            return self::normalizeNestedFileSpec($value);
        }

        return new UploadedFile(
            $value['tmp_name'],
            (int) $value['size'],
            (int) $value['error'],
            $value['name'],
            $value['type']
        );
    }

    /**
     * Normalize an array of file specifications.
     *
     * Loops through all nested files and returns a normalized array of
     * UploadedFileInterface instances.
     *
     * @param array $files
     *
     * @return UploadedFileInterface[]
     */
    private static function normalizeNestedFileSpec(array $files = []): array
    {
        $normalizedFiles = [];

        foreach (\array_keys($files['tmp_name']) as $key) {
            $spec = [
                'tmp_name' => $files['tmp_name'][$key],
                'size' => $files['size'][$key],
                'error' => $files['error'][$key],
                'name' => $files['name'][$key],
                'type' => $files['type'][$key],
            ];

            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }
}
