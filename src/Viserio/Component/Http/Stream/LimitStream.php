<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use Psr\Http\Message\StreamInterface;
use Viserio\Component\Contract\Http\Exception\RuntimeException;

class LimitStream extends AbstractStreamDecorator
{
    /**
     * Stream instance.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    protected $stream;
    /**
     * Offset to start reading from.
     *
     * @var int
     */
    private $offset;

    /**
     * Limit the number of bytes that can be read.
     *
     * @var int
     */
    private $limit;

    /**
     * Stream instance.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    private $stream;

    /**
     * Create a new limit stream instance.
     *
     * @param StreamInterface $stream Stream to wrap
     * @param int             $limit  Total number of bytes to allow to be read
     *                                from the stream. Pass -1 for no limit.
     * @param null|int        $offset position to seek to before reading (only
     *                                works on seekable streams)
     */
    public function __construct(
        StreamInterface $stream,
        int $limit = -1,
        ?int $offset = 0
    ) {
        parent::__construct($stream);

        $this->setLimit($limit);
        $this->setOffset($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        // Always return true if the underlying stream is EOF
        if ($this->stream->eof()) {
            return true;
        }

        // No limit and the underlying stream is not at EOF
        if ($this->limit == -1) {
            return false;
        }

        return $this->stream->tell() >= $this->offset + $this->limit;
    }

    /**
     * Returns the size of the limited subset of data.
     *
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        if (($length = $this->stream->getSize()) === null) {
            return null;
        } elseif ($this->limit == -1) {
            return $length - $this->offset;
        }

        return \min($this->limit, $length - $this->offset);
    }

    /**
     * Allow for a bounded seek on the read limited stream.
     *
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if ($whence !== SEEK_SET || $offset < 0) {
            throw new RuntimeException(\sprintf(
                'Cannot seek to offset %s with whence %s',
                $offset,
                $whence
            ));
        }

        $offset += $this->offset;

        if ($this->limit !== -1 && $offset > $this->offset + $this->limit) {
            $offset = $this->offset + $this->limit;
        }

        $this->stream->seek($offset);
    }

    /**
     * Give a relative tell().
     *
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return $this->stream->tell() - $this->offset;
    }

    /**
     * Set the offset to start limiting from.
     *
     * @param int $offset Offset to seek to and begin byte limiting from
     *
     * @throws \Viserio\Component\Contract\Http\Exception\RuntimeException if the stream cannot be seeked
     *
     * @return void
     */
    public function setOffset(int $offset): void
    {
        $current = $this->stream->tell();

        if ($current !== $offset) {
            // If the stream cannot seek to the offset position, then read to it
            if ($this->stream->isSeekable()) {
                $this->stream->seek($offset);
            } elseif ($current > $offset) {
                throw new RuntimeException(\sprintf('Could not seek to stream offset %s', $offset));
            } else {
                $this->stream->read($offset - $current);
            }
        }

        $this->offset = $offset;
    }

    /**
     * Set the limit of bytes that the decorator allows to be read from the
     * stream.
     *
     * @param int $limit Number of bytes to allow to be read from the stream.
     *                   Use -1 for no limit.
     *
     * @return void
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        if ($this->limit == -1) {
            return $this->stream->read($length);
        }

        // Check if the current position is less than the total allowed
        // bytes + original offset
        $remaining = ($this->offset + $this->limit) - $this->stream->tell();

        if ($remaining > 0) {
            // Only return the amount of requested data, ensuring that the byte
            // limit is not exceeded
            return $this->stream->read(\min($remaining, $length));
        }

        return '';
    }
}
