<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Stream;

use UnexpectedValueException;
use Viserio\Component\Contracts\Filesystem\Exception\FileAccessDeniedException;
use Viserio\Component\Contracts\Filesystem\FileStream;
use Viserio\Component\Encryption\Key;

class ReadOnlyFile implements FileStream
{
    /**
     * The underlying stream resource.
     *
     * @var resource
     */
    private $stream;

    /**
     * The size of the stream.
     *
     * @var int
     */
    private $size;

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
     * ReadOnlyFile constructor.
     *
     * @param string|resource $file
     * @param null|\Viserio\Component\Encryption\Key $key
     *
     * @throws \UnexpectedValueException
     * @throws \Viserio\Component\Contracts\Filesystem\Exception\FileAccessDeniedException
     */
    public function __construct($file, Key $key = null)
    {
        if (is_string($file) && is_file($file)) {
            $fp = \fopen($file, 'rb');

            if (! \is_resource($fp)) {
                throw new FileAccessDeniedException('Could not open file for reading.');
            }

            $this->stream     = $fp;
            $this->closeAfter = true;
            $this->statistics = \fstat($this->fp);
        } elseif (\is_resource($file)) {
            $this->stream     = $file;
            $this->position   = \ftell($this->fp);
            $this->statistics = \fstat($this->fp);
        } else {
            throw new UnexpectedValueException('Invalid stream provided; must be a filename or stream resource.');
        }

        $this->hashKey = $key !== null ? $key->getRawKeyMaterial() : '';
        $this->hash    = $this->getHash();
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
        return $this->statistics['size'];
    }

    /**
     * {@inheritdoc}
     */
    public function write(string $buf, int $num = null): int
    {
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
    {
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingBytes(): int
    {
        return PHP_INT_MAX & ($this->statistics['size'] - $this->position);
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
