<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Provides a buffer stream that can be written to to fill a buffer, and read
 * from to remove bytes from the buffer.
 *
 * This stream returns a "hwm" metadata value that tells upstream consumers
 * what the configured high water mark of the stream is, or the maximum
 * preferred size of the buffer.
 */
class BufferStream implements StreamInterface
{
    /**
     * High water mark.
     *
     * @var int
     */
    private $hwm;

    /**
     * Buffer size.
     *
     * @var string
     */
    private $buffer = '';

    /**
     * Create a new buffer stream instance.
     *
     * @param int $hwm High water mark, representing the preferred maximum
     *                 buffer size. If the size of the buffer exceeds the high
     *                 water mark, then calls to write will continue to succeed
     *                 but will return false to inform writers to slow down
     *                 until the buffer has been drained by reading from it.
     */
    public function __construct(int $hwm = 16384)
    {
        $this->hwm = $hwm;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        try {
            return $this->getContents();
        } catch (RuntimeException) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $buffer       = $this->buffer;
        $this->buffer = '';

        return $buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->buffer = '';
    }

    /**
     * {@inheritdoc}
     */
    public function detach(): void
    {
        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return \mb_strlen($this->buffer);
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new RuntimeException('Cannot seek a BufferStream');
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return $this->buffer === '';
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): void
    {
        throw new RuntimeException('Cannot determine the position of a BufferStream');
    }

    /**
     * Reads data from the buffer.
     *
     * @param mixed $length
     */
    public function read($length)
    {
        $currentLength = \mb_strlen($this->buffer);

        if ($length >= $currentLength) {
            // No need to slice the buffer because we don't have enough data.
            $result       = $this->buffer;
            $this->buffer = '';
        } else {
            // Slice up the result to provide a subset of the buffer.
            $result       = \mb_substr($this->buffer, 0, $length);
            $this->buffer = \mb_substr($this->buffer, $length);
        }

        return $result;
    }

    /**
     * Writes data to the buffer.
     *
     * @param mixed $string
     */
    public function write($string)
    {
        $this->buffer .= $string;

        if (\mb_strlen($this->buffer) >= $this->hwm) {
            return 0;
        }

        return \mb_strlen($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key == 'hwm') {
            return $this->hwm;
        }

        return $key ? null : [];
    }
}
