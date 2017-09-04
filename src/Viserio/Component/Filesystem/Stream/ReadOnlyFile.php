<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Stream;

use Viserio\Component\Contracts\Filesystem\Exception\RuntimeException;
use UnexpectedValueException;
use Viserio\Component\Contracts\Filesystem\Exception\FileAccessDeniedException;
use Viserio\Component\Contracts\Filesystem\Exception\FileModifiedException;
use Viserio\Component\Contracts\Filesystem\Exception\OutOfBoundsException;
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
     * BLAKE2b hash of this file.
     *
     * @var string
     */
    private $hash;

    /**
     * Should TOCTOU test be skipped?
     *
     * @var bool
     */
    private $skipTest = false;

    /**
     * ReadOnlyFile constructor.
     *
     * @param string|resource                        $file
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
            $this->statistics = \fstat($this->stream);
        } elseif (\is_resource($file)) {
            $this->stream     = $file;
            $this->position   = \ftell($this->stream);
            $this->statistics = \fstat($this->stream);
        } else {
            throw new UnexpectedValueException('Invalid stream provided; must be a filename or stream resource.');
        }

        $hashKey    = $key !== null ? $key->getRawKeyMaterial() : '';
        $this->hash = $this->generateHash($hashKey);
    }

    /**
     * Get the calculated BLAKE2b hash of this file.
     *
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Skip TOCTOU test.
     *
     * @param bool $skip
     *
     * @return void
     */
    public function skipTest(bool $skip): void
    {
        $this->skipTest = $skip;
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
        throw new FileAccessDeniedException('This is a read-only file handle.');
    }

    /**
     * {@inheritdoc}
     */
    public function read($length): string
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

        $buf = '';
        $remaining = $length;

        if (! $this->skipTest) {
            $this->toctouTest();
        }

        do {
            if ($remaining <= 0) {
                break;
            }

            /** @var string $read */
            $read = \fread($this->stream, $remaining);

            if (!\is_string($read)) {
                throw new FileAccessDeniedException('Could not read from the file.');
            }

            $buf            .= $read;
            $readSize        = \mb_strlen($read, '8bit');
            $this->position += $readSize;
            $remaining      -= $readSize;
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
        return PHP_INT_MAX & ($this->statistics['size'] - $this->position);
    }

    /**
     * {@inheritdoc}
     */
    public function seek(int $offset): void
    {
        $this->position = $offset;

        if (\fseek($this->stream, $offset, \SEEK_SET) === -1) {
            throw new RuntimeException(
                'Unable to seek to stream position ' . $offset .
                ' with whence SEEK_SET.'
            );
        }
    }

    /**
     * Run-time test to prevent Time-of-check Time-of-use (TOCTOU) attacks (race conditions)
     * through verifying that the hash matches and the current cursor position/file
     * size matches their values when the file was first opened.
     *
     * @link https://cwe.mitre.org/data/definitions/367.html
     *
     * @throws \Viserio\Component\Contracts\Filesystem\Exception\FileModifiedException
     *
     * @return void
     */
    private function toctouTest()
    {
        if (\ftell($this->stream) !== $this->position) {
            throw new FileModifiedException(
                'Read-only file has been modified since it was opened for reading.'
            );
        }

        $stat = \fstat($this->stream);

        if ($stat['size'] !== $this->statistics['size']) {
            throw new FileModifiedException(
                'Read-only file has been modified since it was opened for reading.'
            );
        }
    }

    /**
     * Calculate a BLAKE2b hash of a file.
     *
     * @param string $key
     *
     * @return string
     */
    private function generateHash(string $key): string
    {
        $init = $this->position;

        $this->seek(0);

        $generichash = \sodium_crypto_generichash_init(
            $key,
            \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX
        );

        for ($i = 0; $i < $this->statistics['size']; $i += self::CHUNK) {
            if (($i + self::CHUNK) > $this->statistics['size']) {
                $c = \fread($this->stream, ($this->statistics['size'] - $i));
            } else {
                $c = \fread($this->stream, self::CHUNK);
            }

            \sodium_crypto_generichash_update($generichash, $c);
        }

        // Reset the file pointer's internal cursor to where it was:
        $this->seek($init);

        return \sodium_crypto_generichash_final($generichash);
    }
}
