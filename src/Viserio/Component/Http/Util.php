<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

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
     * @throws \RuntimeException if the file cannot be opened
     *
     * @return resource
     */
    public static function tryFopen($filename, $mode)
    {
        $ex = null;

        set_error_handler(function () use ($filename, $mode, &$ex) {
            $ex = new RuntimeException(sprintf(
                'Unable to open [%s] using mode %s: %s',
                $filename,
                $mode,
                func_get_args()[1]
            ));
        });

        $handle = fopen($filename, $mode);
        restore_error_handler();

        if ($ex) {
            /* @var $ex \RuntimeException */
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
            $len = mb_strlen($buffer);
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
    ) {
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
                $buf = $source->read(min($bufferSize, $remaining));
                $len = mb_strlen($buf);

                if (! $len) {
                    break;
                }

                $remaining -= $len;
                $dest->write($buf);
            }
        }
    }
}
