<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Stream;

use Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException;
use Viserio\Component\Contract\Filesystem\Exception\OutOfBoundsException;
use Viserio\Component\Contract\Filesystem\Exception\UnexpectedValueException;
use Viserio\Component\Contract\Filesystem\FileStream;

class MutableFile implements FileStream
{
    /**
     * Resource modes.
     *
     * @var array
     *
     * @see http://php.net/manual/function.fopen.php
     */
    private const WRITABLE_MODES = [
        'w'   => true, 'w+' => true, 'rw' => true, 'r+' => true, 'x+' => true,
        'c+'  => true, 'wb' => true, 'w+b' => true, 'r+b' => true,
        'x+b' => true, 'c+b' => true, 'w+t' => true, 'r+t' => true,
        'x+t' => true, 'c+t' => true, 'a' => true, 'a+' => true,
    ];
    /**
     * The underlying stream resource.
     *
     * @var resource
     */
    private $stream;

    /**
     * Close after finishing.
     *
     * @var bool
     */
    private $closeAfter = false;

    /**
     * Create a new mutable file instance.
     *
     * @param resource|string $file
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\UnexpectedValueException
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException
     */
    public function __construct($file)
    {
        if (\is_string($file) && \is_file($file)) {
            $fp = \fopen($file, 'wb');

            if (! \is_resource($fp)) {
                throw new FileAccessDeniedException('Could not open file for writing.');
            }

            $this->stream     = $fp;
            $this->closeAfter = true;
        } elseif (\is_resource($file) || (\is_string($file) && \get_resource_type($file) === 'stream')) {
            $meta = \stream_get_meta_data($file);

            if (! isset(self::WRITABLE_MODES[$meta['mode']])) {
                throw new FileAccessDeniedException(
                    \sprintf(
                        'Please choose a writable mode [%s] for your resource.',
                        \implode(', ', array_keys(self::WRITABLE_MODES))
                    )
                );
            }

            $this->stream = $file;
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
        return 0;
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
            throw new OutOfBoundsException('Length parameter cannot be negative.');
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

            $string = \mb_substr($string, $written, null, '8bit');

            $remaining -= $written;
        } while ($remaining > 0);

        return $length;
    }

    /**
     * {@inheritdoc}
     */
    public function read(int $length): string
    {
        return 'Mutable file cannot be read.';
    }

    /**
     * {@inheritdoc}
     */
    public function tell(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getRemainingBytes(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset): void
    {
    }
}
