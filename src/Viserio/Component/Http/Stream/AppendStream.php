<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Stream;

use Psr\Http\Message\StreamInterface;
use Throwable;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Http\Exception\RuntimeException;
use Viserio\Component\Http\Util;

/**
 * Reads from multiple streams, one after the other.
 *
 * This is a read-only stream decorator.
 */
class AppendStream implements StreamInterface
{
    /** @var \Psr\Http\Message\StreamInterface[] Streams being decorated */
    private $streams = [];

    /**
     * @var bool
     */
    private $seekable = true;

    /**
     * @var int
     */
    private $current = 0;

    /**
     * @var int
     */
    private $pos = 0;

    /**
     * Create a new AppendStream instance.
     *
     * @param \Psr\Http\Message\StreamInterface[] $streams Streams to decorate. Each stream must
     *                                                     be readable.
     */
    public function __construct(array $streams = [])
    {
        foreach ($streams as $stream) {
            $this->addStream($stream);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            $this->rewind();

            return $this->getContents();
        } catch (Throwable $exception) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            \trigger_error(self::class . '::__toString exception: ' . (string) $exception, \E_USER_ERROR);

            return '';
        }
    }

    public function isSeekable(): bool
    {
        return $this->seekable;
    }

    /**
     * Add a stream to the AppendStream.
     *
     * @param \Psr\Http\Message\StreamInterface $stream Stream to append. Must be readable.
     *
     * @throws \Viserio\Component\Contract\Http\Exception\InvalidArgumentException if the stream is not readable
     */
    public function addStream(StreamInterface $stream): void
    {
        if (! $stream->isReadable()) {
            throw new InvalidArgumentException('Each stream must be readable.');
        }

        // The stream is only seekable if all streams are seekable
        if (! $stream->isSeekable()) {
            $this->seekable = false;
        }

        $this->streams[] = $stream;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        return Util::copyToString($this);
    }

    /**
     * Closes each attached stream.
     *
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->pos      = $this->current      = 0;
        $this->seekable = true;

        foreach ($this->streams as $stream) {
            $stream->close();
        }

        $this->streams = [];
    }

    /**
     * Detaches each attached stream.
     *
     * Returns null as it's not clear which underlying stream resource to return.
     *
     * {@inheritdoc}
     */
    public function detach(): void
    {
        $this->pos      = $this->current      = 0;
        $this->seekable = true;

        foreach ($this->streams as $stream) {
            $stream->detach();
        }

        $this->streams = [];
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return $this->pos;
    }

    /**
     * Tries to calculate the size by adding the size of each stream.
     *
     * If any of the streams do not return a valid number, then the size of the
     * append stream cannot be determined and null is returned.
     *
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        $size = 0;

        foreach ($this->streams as $stream) {
            $s = $stream->getSize();

            if ($s === null) {
                return null;
            }

            $size += $s;
        }

        return $size;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return ! $this->streams ||
            ($this->current >= \count($this->streams) - 1 &&
                $this->streams[$this->current]->eof());
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Attempts to seek to the given position. Only supports SEEK_SET.
     *
     * {@inheritdoc}
     */
    public function seek($offset, $whence = \SEEK_SET): void
    {
        if (! $this->seekable) {
            throw new RuntimeException('This AppendStream is not seekable.');
        }

        if ($whence !== \SEEK_SET) {
            throw new RuntimeException('The AppendStream can only seek with SEEK_SET.');
        }

        $this->pos = $this->current = 0;

        // Rewind each stream
        foreach ($this->streams as $i => $stream) {
            try {
                $stream->rewind();
            } catch (Throwable $e) {
                throw new RuntimeException('Unable to seek stream '
                    . $i . ' of the AppendStream', 0, $e);
            }
        }

        // Seek to the actual position by reading from each stream
        while ($this->pos < $offset && ! $this->eof()) {
            $result = $this->read(\min(8096, $offset - $this->pos));

            if ($result === '') {
                break;
            }
        }
    }

    /**
     * Reads from all of the appended streams until the length is met or EOF.
     *
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $buffer         = '';
        $total          = \count($this->streams) - 1;
        $remaining      = $length;
        /** @var bool $progressToNext */
        $progressToNext = false;

        while ($remaining > 0) {
            // Progress to the next stream if needed.
            if ($progressToNext || $this->streams[$this->current]->eof()) {
                $progressToNext = false;

                if ($this->current === $total) {
                    break;
                }

                $this->current++;
            }

            $result = $this->streams[$this->current]->read($remaining);

            // Using a loose comparison here to match on '', false, and null
            if ($result === null) {
                $progressToNext = true;

                continue;
            }

            $buffer .= $result;
            $remaining = $length - \strlen($buffer);
        }

        $this->pos += \strlen($buffer);

        return $buffer;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        throw new RuntimeException('Cannot write to an AppendStream.');
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }
}
