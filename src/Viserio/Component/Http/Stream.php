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

namespace Viserio\Component\Http;

use Psr\Http\Message\StreamInterface;
use Throwable;
use Viserio\Contract\Http\Exception\RuntimeException;
use Viserio\Contract\Http\Exception\UnexpectedValueException;

class Stream implements StreamInterface
{
    /**
     * Bit mask to determine if the stream is a pipe.
     *
     * This is octal as per header stat.h
     *
     * @var int
     */
    public const FSTAT_MODE_S_IFIFO = 0010000;

    /**
     * Resource modes.
     *
     * @var string
     *
     * @see http://php.net/manual/function.fopen.php
     * @see http://php.net/manual/en/function.gzopen.php
     */
    public const READABLE_MODES = '/r|a\+|ab\+|w\+|wb\+|x\+|xb\+|c\+|cb\+/';

    public const WRITABLE_MODES = '/a|w|r\+|rw|x|c/';

    /**
     * The underlying stream resource.
     *
     * @var null|resource
     */
    protected $stream;

    /**
     * Stream metadata.
     *
     * @var array<int|string, string>
     */
    protected $metadata;

    /**
     * Is this stream readable?
     *
     * @var bool
     */
    protected bool $readable;

    /**
     * Is this stream writable?
     *
     * @var bool
     */
    protected bool $writable;

    /**
     * Is this stream seekable?
     *
     * @var bool
     */
    protected bool $seekable;

    /**
     * The size of the stream if known.
     *
     * @var null|int
     */
    protected ?int $size;

    /** @var string */
    protected $uri;

    /**
     * Is this stream a pipe?
     *
     * @var null|bool
     */
    protected ?bool $isPipe;

    /**
     * Stream type of a open stream.
     *
     * @var string
     */
    protected string $streamType;

    /**
     * This constructor accepts an associative array of options.
     *
     * - size: (int) If a read stream would otherwise have an indeterminate
     *   size, but the size is known due to foreknowledge, then you can
     *   provide that size, in bytes.
     * - metadata: (array) Any additional metadata to return when the metadata
     *   of the stream is accessed.
     *
     * @param resource|string          $stream  stream resource to wrap
     * @param array<int|string, mixed> $options associative array of options
     *                                          array[]
     *                                          ['mode']      string                     A optional option; Default mode is 'rb' for the string stream
     *                                          ['size']      int                        A optional option; Size of the stream
     *                                          ['metadata']  array<int|string, string>  A optional option; Metadata of the stream
     *
     * @throws \Viserio\Contract\Http\Exception\UnexpectedValueException if the stream is not a stream resource
     */
    public function __construct($stream, array $options = [])
    {
        if (\is_string($stream)) {
            $stream = Util::tryFopen($stream, $options['mode'] ?? 'rb');
        } elseif (! \is_resource($stream) || \get_resource_type($stream) !== 'stream') {
            throw new UnexpectedValueException('Invalid stream provided; must be a string stream identifier or stream resource.');
        }

        $this->isPipe = null;
        $this->size = null;

        $this->stream = $stream;

        if (\array_key_exists('size', $options)) {
            $this->size = (int) $options['size'];
        }

        $this->metadata = $options['metadata'] ?? [];

        $meta = \stream_get_meta_data($this->stream);

        $this->seekable = ! $this->isPipe() && $meta['seekable'];
        $this->readable = \preg_match(self::READABLE_MODES, $meta['mode']) === 1 || $this->isPipe();
        $this->writable = \preg_match(self::WRITABLE_MODES, $meta['mode']) === 1;
        $this->uri = $this->getMetadata('uri');
        $this->streamType = $meta['stream_type'] ?? 'unknown';
    }

    /**
     * Closes the stream when the destructed.
     *
     * @return void
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString(): string
    {
        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (Throwable $exception) {
            // Really, PHP? https://bugs.php.net/bug.php?id=53648
            \trigger_error(self::class . '::__toString exception: ' . (string) $exception, \E_USER_ERROR);

            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (! isset($this->stream)) {
            return $key !== null ? null : [];
        }

        if ($key === null) {
            return $this->metadata + \stream_get_meta_data($this->stream);
        }

        if (\array_key_exists($key, $this->metadata)) {
            return $this->metadata[$key];
        }

        $meta = \stream_get_meta_data($this->stream);

        return $meta[$key] ?? null;
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
    public function getSize(): ?int
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (! isset($this->stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->uri !== null) {
            \clearstatcache(true, $this->uri);
        }

        $stats = \fstat($this->stream);

        if ($stats !== false && ! $this->isPipe()) {
            $this->size = $stats['size'];
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): string
    {
        if (! isset($this->stream)) {
            throw new RuntimeException('Stream is detached.');
        }

        $contents = \stream_get_contents($this->stream);

        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents.');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        if (isset($this->stream)) {
            if (\is_resource($this->stream)) {
                if ($this->isPipe()) {
                    \pclose($this->stream);
                } elseif ($this->streamType === 'ZLIB') {
                    \gzclose($this->stream);
                } else {
                    \fclose($this->stream);
                }
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

        $this->stream = null;

        $this->uri = '';
        $this->metadata = [];
        $this->size = $this->isPipe = null;
        $this->readable = $this->writable = $this->seekable = false;

        return $result;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If Stream is detached
     */
    public function eof(): bool
    {
        if (! isset($this->stream)) {
            throw new RuntimeException('Stream is detached.');
        }

        return \feof($this->stream);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If Stream is detached
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If unable to determine stream position
     */
    public function tell(): int
    {
        if (! isset($this->stream)) {
            throw new RuntimeException('Stream is detached.');
        }

        $result = \ftell($this->stream);

        if (! \is_int($result) || $this->isPipe()) {
            throw new RuntimeException('Unable to determine stream position.');
        }

        return $result;
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
     *
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If Stream is detached
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If Stream is not seekable
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If unable to determine stream position
     */
    public function seek($offset, $whence = \SEEK_SET): void
    {
        if (! isset($this->stream)) {
            throw new RuntimeException('Stream is detached.');
        }

        if (! $this->seekable) {
            throw new RuntimeException('Stream is not seekable.');
        }

        if (\fseek($this->stream, $offset, $whence) === -1) {
            throw new RuntimeException('Unable to seek to stream position ' . $offset . ' with whence ' . \var_export($whence, true) . '.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If Stream is detached
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If cannot read from non-readable stream
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If length parameter cannot be negative
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If unable to read from stream
     */
    public function read($length): string
    {
        if (! isset($this->stream)) {
            throw new RuntimeException('Stream is detached.');
        }

        if (! $this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream.');
        }

        if ($length < 0) {
            throw new RuntimeException('Length parameter cannot be negative.');
        }

        if ($length === 0) {
            return '';
        }

        $string = fread($this->stream, $length);

        if ($string === false) {
            throw new RuntimeException('Unable to read from stream.');
        }

        return $string;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If Stream is detached
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If cannot write to a non-writable stream
     * @throws \Viserio\Contract\Http\Exception\RuntimeException If unable to write to stream
     */
    public function write($string): int
    {
        if (! isset($this->stream)) {
            throw new RuntimeException('Stream is detached.');
        }

        if (! $this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream.');
        }

        // We can't know the size after writing anything
        $this->size = null;
        $result = \fwrite($this->stream, $string);

        if ($result === false) {
            throw new RuntimeException('Unable to write to stream.');
        }

        return $result;
    }

    /**
     * Returns whether or not the stream is a pipe.
     *
     * @return bool
     */
    private function isPipe(): bool
    {
        if ($this->isPipe === null) {
            $this->isPipe = false;

            if (\is_resource($this->stream)) {
                $stats = \fstat($this->stream);

                if ($stats !== false) {
                    $this->isPipe = ($stats['mode'] & self::FSTAT_MODE_S_IFIFO) !== 0;
                }
            }
        }

        return $this->isPipe;
    }
}
