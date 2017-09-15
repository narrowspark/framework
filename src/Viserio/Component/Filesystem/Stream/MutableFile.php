<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Stream;

use Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException;
use Viserio\Component\Contract\Filesystem\Exception\OutOfBoundsException;
use Viserio\Component\Contract\Filesystem\Exception\RuntimeException;
use Viserio\Component\Contract\Filesystem\Exception\UnexpectedValueException;
use Viserio\Component\Contract\Filesystem\FileStream;

class MutableFile implements FileStream
{
    /**
     * The underlying stream resource.
     *
     * @var resource
     */
    private $stream;

    /**
     * The actual position.
     *
     * @var int
     */
    private $position = 0;

    /**
     * Close after finishing.
     *
     * @var bool
     */
    private $closeAfter = false;

    /**
     * Statistics of the file.
     *
     * @var array
     */
    private $statistics = [];

    /**
     * Create a new mutable file instance.
     *
     * @param string|resource $file
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\UnexpectedValueException
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException
     */
    public function __construct($file)
    {
        if (is_string($file) && is_file($file)) {
            $fp = \fopen($file, 'wb');

            if (! \is_resource($fp)) {
                throw new FileAccessDeniedException('Could not open file for reading.');
            }

            $this->stream     = $fp;
            $this->closeAfter = true;
            $this->statistics = \fstat($this->stream);
        } elseif (\is_resource($file) || \get_resource_type($file) === 'stream') {
            $this->stream     = $file;
            $this->position   = \ftell($this->stream);
            $this->statistics = \fstat($this->stream);
        } else {
            throw new UnexpectedValueException('Invalid stream provided; must be a filename or stream resource.');
        }
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
    public function close(): void
    {
        if ($this->closeAfter) {
            $this->closeAfter = false;

            \fclose($this->stream);
            \clearstatcache();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSize(): int
    {
        $stat = \fstat($this->stream);

        return $stat['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $string, int $length = null): int
    {
        $bufSize = \mb_strlen($string, '8bit');

        if ($length === null || $length > $bufSize) {
            $length = $bufSize;
        }

        if ($length < 0) {
            throw new OutOfBoundsException('Length parameter cannot be negative');
        }

        $remaining = $length;

        do {
            if ($remaining <= 0) {
                break;
            }

            $written = \fwrite($this->stream, $string, $remaining);

            if ($written === false) {
                throw new FileAccessDeniedException('Could not write to the file.');
            }

            $string           = \mb_substr($string, $written, null, '8bit');
            $this->position += $written;
            $this->statistics = \fstat($this->stream);

            $remaining -= $written;
        } while ($remaining > 0);

        return $length;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length): string
    {
        if ($length < 0) {
            throw new OutOfBoundsException('Length parameter cannot be negative');
        }

        if ($length === 0) {
            return '';
        }

        if (($this->position + $length) > $this->statistics['size']) {
            throw new OutOfBoundsException('Out-of-bounds read.');
        }

        $buf       = '';
        $remaining = $length;

        do {
            if ($remaining <= 0) {
                break;
            }

            /** @var string $read */
            $read = \fread($this->stream, $remaining);

            if (! \is_string($read)) {
                throw new FileAccessDeniedException('Could not read from the file.');
            }

            $buf .= $read;
            $readSize = \mb_strlen($read, '8bit');

            $this->position += $readSize;

            $remaining -= $readSize;
        } while ($remaining > 0);

        return $buf;
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return \ftell($this->stream);
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingBytes(): int
    {
        $stat = \fstat($this->stream);
        $pos  = \ftell($this->stream);

        return PHP_INT_MAX & ($stat['size'] - $pos);
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset): void
    {
        $this->position = $offset;

        if (\fseek($this->stream, $offset, SEEK_SET) === -1) {
            throw new RuntimeException(
                'Unable to seek to stream position ' . $offset .
                ' with whence SEEK_SET.'
            );
        }
    }
}
