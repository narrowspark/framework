<?php
namespace Viserio\Http\Stream;

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
    private $hwm;
    private $buffer = '';

    /**
     * @param int $hwm High water mark, representing the preferred maximum
     *                 buffer size. If the size of the buffer exceeds the high
     *                 water mark, then calls to write will continue to succeed
     *                 but will return false to inform writers to slow down
     *                 until the buffer has been drained by reading from it.
     */
    public function __construct($hwm = 16384)
    {
        $this->hwm = $hwm;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getContents();
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        $buffer = $this->buffer;
        $this->buffer = '';

        return $buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->buffer = '';
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return strlen($this->buffer);
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
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        throw new RuntimeException('Cannot seek a BufferStream');
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return strlen($this->buffer) === 0;
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        throw new RuntimeException('Cannot determine the position of a BufferStream');
    }

    /**
     * Reads data from the buffer.
     */
    public function read($length)
    {
        $currentLength = strlen($this->buffer);

        if ($length >= $currentLength) {
            // No need to slice the buffer because we don't have enough data.
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            // Slice up the result to provide a subset of the buffer.
            $result = substr($this->buffer, 0, $length);
            $this->buffer = substr($this->buffer, $length);
        }

        return $result;
    }

    /**
     * Writes data to the buffer.
     */
    public function write($string)
    {
        $this->buffer .= $string;

        // TODO: What should happen here?
        if (strlen($this->buffer) >= $this->hwm) {
            return false;
        }

        return strlen($string);
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
