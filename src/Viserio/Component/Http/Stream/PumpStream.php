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
use Viserio\Component\Http\Util;
use Viserio\Contract\Http\Exception\RuntimeException;

class PumpStream implements StreamInterface
{
    /**
     * The size of the stream if known.
     *
     * @var null|int
     */
    protected $size;

    /**
     * Source of the stream data.
     *
     * @var null|callable
     */
    private $source;

    /** @var int */
    private $tellPos = 0;

    /**
     * Stream metadata.
     *
     * @var array<int|string, int|string>
     */
    private $metadata;

    /**
     * Buffer stream instance.
     *
     * @var \Viserio\Component\Http\Stream\BufferStream
     */
    private $buffer;

    /**
     * Create a new pump stream instance.
     *
     * @param callable                 $source  Source of the stream data. The callable MAY
     *                                          accept an integer argument used to control the
     *                                          amount of data to return. The callable MUST
     *                                          return a string when called, or false on error
     *                                          or EOF.
     * @param array<int|string, mixed> $options stream options:
     *                                          - metadata: Hash of metadata to use with stream.
     *                                          - size: Size of the stream, if known
     */
    public function __construct(callable $source, array $options = [])
    {
        $this->source = $source;
        $this->size = $options['size'] ?? null;
        $this->metadata = $options['metadata'] ?? [];
        $this->buffer = new BufferStream();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            return Util::copyToString($this);
        } catch (Throwable $exception) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            trigger_error(\sprintf('%s::__toString exception: %s', self::class, (string) $exception), \E_USER_ERROR);

            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        $this->detach();
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $this->tellPos = 0;
        $this->source = null;

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return $this->tellPos;
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->source === null;
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
        throw new RuntimeException('Cannot seek a PumpStream.');
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
     *
     * @throws \Viserio\Contract\Http\Exception\RuntimeException
     */
    public function write($string): int
    {
        throw new RuntimeException('Cannot write to a PumpStream.');
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
    public function read($length): string
    {
        $data = $this->buffer->read($length);
        $readLen = \strlen($data);
        $this->tellPos += $readLen;
        $remaining = $length - $readLen;

        if ($remaining !== 0) {
            $this->pump($remaining);
            $data .= $this->buffer->read($remaining);
            $this->tellPos += \strlen($data) - $readLen;
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        $result = '';

        while (! $this->eof()) {
            $result .= $this->read(1000000);
        }

        return $result;
    }

    /**
     * @param int $length
     *
     * @return void
     */
    private function pump(int $length): void
    {
        if ($this->source !== null) {
            do {
                $data = \call_user_func($this->source, $length);

                if ($data === false || $data === null) {
                    $this->source = null;

                    return;
                }

                $this->buffer->write($data);

                $length -= \strlen($data);
            } while ($length > 0);
        }
    }
}
