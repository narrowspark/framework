<?php
namespace Viserio\Http;

use InvalidArgumentException;
use Iterator;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Viserio\Http\Stream\PumpStream;

class Util
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
     * Create a new stream based on the input type.
     *
     * Options is an associative array that can contain the following keys:
     * - metadata: Array of custom metadata.
     * - size: Size of the stream.
     *
     * @param resource|string|null|int|float|bool|StreamInterface|callable $resource Entity body data
     * @param array                                                        $options  Additional options
     *
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     *
     * @return Stream
     */
    public static function getStream($resource = '', array $options = []): StreamInterface
    {
        if (is_scalar($resource)) {
            $stream = fopen('php://temp', 'r+');

            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }

            return new Stream($stream, $options);
        }

        switch (gettype($resource)) {
            case 'resource':
                return new Stream($resource, $options);
            case 'object':
                if ($resource instanceof StreamInterface) {
                    return $resource;
                } elseif ($resource instanceof Iterator) {
                    return new PumpStream(function () use ($resource) {
                        if (! $resource->valid()) {
                            return false;
                        }

                        $result = $resource->current();
                        $resource->next();

                        return $result;
                    }, $options);
                } elseif (method_exists($resource, '__toString')) {
                    return self::getStream((string) $resource, $options);
                }

                break;
            case 'NULL':
                return new Stream(fopen('php://temp', 'r+'), $options);
        }

        if (is_callable($resource)) {
            return new PumpStream($resource, $options);
        }

        throw new InvalidArgumentException('Invalid resource type: ' . gettype($resource));
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
                'Unable to open %s using mode %s: %s',
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
     * @param StreamInterface $stream Stream to read
     * @param int             $maxLen Maximum number of bytes to read. Pass -1
     *                                to read the entire stream.
     *
     * @throws \RuntimeException on error.
     *
     * @return string
     */
    public static function copyToString(StreamInterface $stream, $maxLen = -1): string
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
            $len = strlen($buffer);
        }

        return $buffer;
    }

    /**
     * Copy the contents of a stream into another stream until the given number
     * of bytes have been read.
     *
     * @param StreamInterface $source Stream to read from
     * @param StreamInterface $dest   Stream to write to
     * @param int             $maxLen Maximum number of bytes to read. Pass -1
     *                                to read the entire stream.
     *
     * @throws \RuntimeException on error.
     */
    public static function copyToStream(
        StreamInterface $source,
        StreamInterface $dest,
        $maxLen = -1
    ) {
        if ($maxLen === -1) {
            while (! $source->eof()) {
                if (! $dest->write($source->read(1048576))) {
                    break;
                }
            }

            return;
        }

        $bytes = 0;

        while (! $source->eof()) {
            $buf = $source->read($maxLen - $bytes);

            if (! ($len = strlen($buf))) {
                break;
            }

            $bytes += $len;
            $dest->write($buf);

            if ($bytes == $maxLen) {
                break;
            }
        }
    }
}
