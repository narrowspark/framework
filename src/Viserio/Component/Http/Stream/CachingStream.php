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
use Viserio\Component\Http\Stream;
use Viserio\Component\Http\Util;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

class CachingStream extends AbstractStreamDecorator
{
    /** @var \Psr\Http\Message\StreamInterface */
    protected $stream;

    /**
     * Stream being wrapped.
     *
     * @var \Psr\Http\Message\StreamInterface
     */
    private $remoteStream;

    /**
     * Number of bytes to skip reading due to a write on the buffer.
     *
     * @var int
     */
    private $skipReadBytes = 0;

    /**
     * We will treat the buffer object as the body of the stream.
     *
     * @param \Psr\Http\Message\StreamInterface      $stream Stream to cache
     * @param null|\Psr\Http\Message\StreamInterface $target Optionally specify where data is cached
     */
    public function __construct(StreamInterface $stream, ?StreamInterface $target = null)
    {
        $this->remoteStream = $stream;

        /** @var resource $handle */
        $handle = \fopen('php://temp', 'r+');

        parent::__construct($target ?? new Stream($handle));
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): ?int
    {
        return \max($this->stream->getSize(), $this->remoteStream->getSize());
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = \SEEK_SET): void
    {
        if ($whence === \SEEK_SET) {
            $byte = $offset;
        } elseif ($whence === \SEEK_CUR) {
            $byte = $offset + $this->tell();
        } elseif ($whence === \SEEK_END) {
            $size = $this->remoteStream->getSize();

            if ($size === null) {
                $size = $this->cacheEntireStream();
            }
            $byte = $size + $offset;
        } else {
            throw new InvalidArgumentException('Invalid whence.');
        }

        $diff = $byte - (int) $this->stream->getSize();

        if ($diff > 0) {
            // Read the remoteStream until we have read in at least the amount
            // of bytes requested, or we reach the end of the file.
            while ($diff > 0 && ! $this->remoteStream->eof()) {
                $this->read($diff);
                $diff = $byte - (int) $this->stream->getSize();
            }
        } else {
            // We can just do a normal seek since we've already seen this byte.
            $this->stream->seek($byte);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
        // Perform a regular read on any previously read data from the buffer
        $data = $this->stream->read($length);
        $remaining = $length - \strlen($data);

        // More data was requested so read from the remote stream
        if ($remaining > 0) {
            // If data was written to the buffer in a position that would have
            // been filled from the remote stream, then we must skip bytes on
            // the remote stream to emulate overwriting bytes from that
            // position. This mimics the behavior of other PHP stream wrappers.
            $remoteData = $this->remoteStream->read(
                $remaining + $this->skipReadBytes
            );

            if ($this->skipReadBytes > 0) {
                $len = \strlen($remoteData);
                $remoteData = \substr($remoteData, $this->skipReadBytes);
                $this->skipReadBytes = \max(0, $this->skipReadBytes - $len);
            }

            $data .= $remoteData;
            $this->stream->write($remoteData);
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function write($string): int
    {
        // When appending to the end of the currently read stream, you'll want
        // to skip bytes from being read from the remote stream to emulate
        // other stream wrappers. Basically replacing bytes of data of a fixed
        // length.
        $overflow = (\strlen($string) + $this->tell()) - $this->remoteStream->tell();

        if ($overflow > 0) {
            $this->skipReadBytes += $overflow;
        }

        return $this->stream->write($string);
    }

    /**
     * {@inheritdoc}
     */
    public function eof(): bool
    {
        return $this->stream->eof() && $this->remoteStream->eof();
    }

    /**
     * Close both the remote stream and buffer stream.
     *
     * @return void
     */
    public function close(): void
    {
        $this->remoteStream->close();
        $this->stream->close();
    }

    /**
     * @return int
     */
    private function cacheEntireStream(): int
    {
        $target = new FnStream(['write' => 'strlen']);

        Util::copyToStream($this, $target);

        return $this->tell();
    }
}
