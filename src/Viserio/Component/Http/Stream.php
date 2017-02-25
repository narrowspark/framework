<?php
declare(strict_types=1);
namespace Viserio\Component\Http;

use BadMethodCallException;
use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Throwable;

class Stream implements StreamInterface
{
    /**
     * Bit mask to determine if the stream is a pipe.
     *
     * This is octal as per header stat.h
     */
    public const FSTAT_MODE_S_IFIFO = 0010000;

    /**
     * Resource modes.
     *
     * @var array
     *
     * @link http://php.net/manual/function.fopen.php
     */
    public const READABLE_MODES = [
        'r'   => true, 'w+' => true, 'r+' => true, 'x+' => true, 'c+' => true,
        'rb'  => true, 'w+b' => true, 'r+b' => true, 'x+b' => true,
        'c+b' => true, 'rt' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a+' => true,
    ];

    public const WRITABLE_MODES = [
        'w'   => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
        'c+'  => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
        'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
    ];

    /**
     * The underlying stream resource.
     *
     * @var resource
     */
    protected $stream;

    /**
     * Stream metadata.
     *
     * @var array
     */
    protected $meta;

    /**
     * Is this stream readable?
     *
     * @var bool
     */
    protected $readable;

    /**
     * Is this stream writable?
     *
     * @var bool
     */
    protected $writable;

    /**
     * Is this stream seekable?
     *
     * @var bool
     */
    protected $seekable;

    /**
     * The size of the stream if known.
     *
     * @var null|int
     */
    protected $size;

    /**
     * @var string
     */
    protected $uri;

    /**
     * This constructor accepts an associative array of options.
     *
     * - size: (int) If a read stream would otherwise have an indeterminate
     *   size, but the size is known due to foreknowledge, then you can
     *   provide that size, in bytes.
     * - metadata: (array) Any additional metadata to return when the metadata
     *   of the stream is accessed.
     *
     * @param resource $stream  stream resource to wrap
     * @param array    $options associative array of options
     *
     * @throws \InvalidArgumentException if the stream is not a stream resource
     */
    public function __construct($stream, array $options = [])
    {
        if (! is_resource($stream) || get_resource_type($stream) !== 'stream') {
            throw new InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or stream resource'
            );
        }

        $this->stream = $stream;

        if (isset($options['size'])) {
            $this->size = (int) $options['size'];
        }

        $this->meta = isset($options['metadata'])
            ? $options['metadata']
            : [];

        $meta = stream_get_meta_data($this->stream);

        $this->seekable = $meta['seekable'];
        $this->readable = isset(self::READABLE_MODES[$meta['mode']]);
        $this->writable = isset(self::WRITABLE_MODES[$meta['mode']]);

        $this->uri = $this->getMetadata('uri');
    }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    public function __get($name)
    {
        if ($name == 'stream') {
            throw new RuntimeException('The stream is detached');
        }

        throw new BadMethodCallException('No value for ' . $name);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        try {
            $this->seek(0);

            return (string) stream_get_contents($this->stream);
        } catch (Throwable $exception) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        $contents = stream_get_contents($this->stream);

        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }

            $this->detach();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        if (! isset($this->stream)) {
            return null;
        }

        $result = $this->stream;

        unset($this->stream);

        $this->size     = 'null';
        $this->uri      = '';
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return (int) $this->size;
        }

        if (! isset($this->stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);

        if (isset($stats['size'])) {
            $this->size = (int) $stats['size'];

            return (int) $this->size;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return $this->readable;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return $this->writable;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return ! $this->stream || feof($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        $result = ftell($this->stream);

        if ($result === false) {
            throw new RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (! $this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        } elseif (fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException(
                'Unable to seek to stream position '
                . $offset . ' with whence ' . var_export($whence, true)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        if (! $this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }

        if ($length < 0) {
            throw new RuntimeException('Length parameter cannot be negative');
        }

        if ($length === 0) {
            return '';
        }

        $string = fread($this->stream, $length);

        if ($string === false) {
            throw new RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        if (! $this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;
        $result     = fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (! isset($this->stream)) {
            return $key ? null : [];
        } elseif (! $key) {
            return $this->meta + stream_get_meta_data($this->stream);
        } elseif (isset($this->meta[$key])) {
            return $this->meta[$key];
        }

        $meta = stream_get_meta_data($this->stream);

        return isset($meta[$key]) ? $meta[$key] : null;
    }
}
