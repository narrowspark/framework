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

namespace Viserio\Component\Http\Stream;

use Psr\Http\Message\StreamInterface;
use Throwable;
use Viserio\Contract\Http\Exception\RuntimeException;

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
    public function __toString(): string
    {
        try {
            return $this->getContents();
        } catch (Throwable $exception) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            trigger_error(\sprintf('%s::__toString exception: %s', self::class, (string) $exception), \E_USER_ERROR);

            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        $buffer = $this->buffer;
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
    public function detach()
    {
        $this->close();

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return \strlen($this->buffer);
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
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable(): bool
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
    public function seek($offset, $whence = \SEEK_SET): void
    {
        throw new RuntimeException('Cannot seek a BufferStream.');
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->buffer === '';
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        throw new RuntimeException('Cannot determine the position of a BufferStream.');
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        $currentLength = \strlen($this->buffer);

        if ($length >= $currentLength) {
            // No need to slice the buffer because we don't have enough data.
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            // Slice up the result to provide a subset of the buffer.
            $result = \substr($this->buffer, 0, $length);
            $this->buffer = \substr($this->buffer, $length);
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        $this->buffer .= $string;

        if (\strlen($this->buffer) >= $this->hwm) {
            return 0;
        }

        return \strlen($string);
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === 'hwm') {
            return $this->hwm;
        }

        return $key !== null ? null : [];
    }
}
