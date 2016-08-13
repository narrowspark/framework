<?php
declare(strict_types=1);
namespace Viserio\Http\Stream;

use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Viserio\Contracts\Http\Exceptions\ByteCountingStreamException;

/**
 *  Stream decorator that ensures an expected number of bytes can be read
 *  from an underlying read stream. \RuntimeException is thrown when
 *  the underlying stream fails to provide the expected number of bytes.
 *  Excess bytes will be ignored.
 */
class ByteCountingStream extends AbstractStreamDecorator
{
    /**
     * Number of bytes remains to be read´.
     *
     * @var int
     */
    private $remaining;

    /**
     * @param StreamInterface $stream       Stream to wrap
     * @param int             $bytesToRead  Number of bytes to read
     *
     * @throws \Viserio\Contracts\Http\Exception\ByteCountingStreamException|\InvalidArgumentException
     */
    public function __construct(StreamInterface $stream, int $bytesToRead)
    {
        $this->stream = $stream;

        if (!is_int($bytesToRead) || $bytesToRead < 0) {
            $msg = 'Bytes to read should be a non-negative integer for '
                . sprintf('ByteCountingStream, got %s.', $bytesToRead);
            throw new InvalidArgumentException($msg);
        }

        if ($this->stream->getSize() !== null &&
            $bytesToRead > $this->stream->getSize()
        ) {
            throw new ByteCountingStreamException(
                $bytesToRead,
                $this->stream->getSize()
            );
        }

        $this->remaining = $bytesToRead;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Contracts\Http\Exception\ByteCountingStreamException
     */
    public function read($length)
    {
        if ($this->remaining === 0) {
            return '';
        }

        $offset = $this->tell();
        $bytesToRead = min($length, $this->remaining);
        $data = $this->stream->read($bytesToRead);

        $this->remaining -= strlen($data);

        if ((!$data || $data === '') && $this->remaining !== 0) {
            // hits EOF
            $provide = $this->tell() - $offset;

            throw new ByteCountingStreamException($this->remaining, $provide);
        }

        return $data;
    }
}
