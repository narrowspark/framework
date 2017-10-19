<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\RuntimeException;

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
    /**
     * Private constructor; non-instantiable.
     *
     * @codeCoverageIgnore
     */
    private function __construct()
    {
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
     * @throws \Viserio\Component\Contract\Http\Exception\RuntimeException if the file cannot be opened
     *
     * @return resource
     */
    public static function tryFopen(string $filename, string $mode)
    {
        $ex = null;

        \set_error_handler(function () use ($filename, $mode, &$ex): void {
            $ex = new RuntimeException(\sprintf(
                'Unable to open [%s] using mode %s: %s',
                $filename,
                $mode,
                \func_get_args()[1]
            ));
        });

        $handle = \fopen($filename, $mode);
        \restore_error_handler();

        if ($ex) {
            // @var $ex \RuntimeException
            throw $ex;
        }

        return $handle;
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
                if ($buf == null) {
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
            if ($buf == null) {
                break;
            }

            $buffer .= $buf;
            $len = \mb_strlen($buffer);
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
    public static function copyToStream(
        StreamInterface $source,
        StreamInterface $dest,
        int $maxLen = -1
    ): void {
        if ($maxLen === -1) {
            while (! $source->eof()) {
                if (! $dest->write($source->read(1048576))) {
                    break;
                }
            }

            return;
        }

        $bufferSize = 8192;

        if ($maxLen === -1) {
            while (! $source->eof()) {
                if (! $dest->write($source->read($bufferSize))) {
                    break;
                }
            }
        } else {
            $remaining = $maxLen;

            while ($remaining > 0 && ! $source->eof()) {
                $buf = $source->read(\min($bufferSize, $remaining));
                $len = \mb_strlen($buf);

                if (! $len) {
                    break;
                }

                $remaining -= $len;
                $dest->write($buf);
            }
        }
    }

    /**
     * Return an UploadedFile instance array.
     *
     * @param array $files A array which respect $_FILES structure
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException for unrecognized values
     *
     * @return array
     */
    public static function normalizeFiles(array $files): array
    {
        $normalized = [];
        foreach ($files as $key => $value) {
            if ($value instanceof UploadedFileInterface) {
                $normalized[$key] = $value;
            } elseif (\is_array($value) && isset($value['tmp_name'])) {
                $normalized[$key] = self::createUploadedFileFromSpec($value);
            } elseif (\is_array($value)) {
                $normalized[$key] = self::normalizeFiles($value);

                continue;
            } else {
                throw new InvalidArgumentException('Invalid value in files specification');
            }
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
                'size'     => $files['size'][$key],
                'error'    => $files['error'][$key],
                'name'     => $files['name'][$key],
                'type'     => $files['type'][$key],
            ];

            $normalizedFiles[$key] = self::createUploadedFileFromSpec($spec);
        }

        return $normalizedFiles;
    }
}
