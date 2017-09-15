<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Filesystem;

interface FileStream
{
    /**
     * Closes the stream and any underlying resources.
     *
     * @return void
     */
    public function close(): void;

    /**
     * Get the size of the buffer.
     *
     * @return int Returns the size in bytes.
     */
    public function getSize(): int;

    /**
     * Write to a stream; prevent partial writes.
     *
     * @param string   $string
     * @param int|null $length (number of bytes)
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Component\Contract\Filesystem\Exception\OutOfBoundsException
     *
     * @return int Returns the number of bytes written to the stream.
     */
    public function write(string $string, int $length = null): int;

    /**
     * Read from a stream; prevent partial reads.
     *
     * @param int $length Read up to $length bytes from the object and return
     *                    them. Fewer than $length bytes may be returned if underlying stream
     *                    call returns fewer bytes.
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileAccessDeniedException
     * @throws \Viserio\Component\Contract\Filesystem\Exception\OutOfBoundsException
     *
     * @return string Returns the data read from the stream, or an empty string
     *                if no bytes are available.
     */
    public function read(int $length): string;

    /**
     * Returns the current position of the file read/write pointer.
     *
     * @return int Position of the file pointer
     */
    public function tell(): int;

    /**
     * How many bytes are left between here and the end of the stream?
     *
     * @return int
     */
    public function getRemainingBytes(): int;

    /**
     * Seek to a position in the stream.
     *
     * @link http://www.php.net/manual/en/function.fseek.php
     *
     * @param int $offset Stream offset
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\RuntimeException on failure.
     *
     * @return void
     */
    public function seek(int $offset): void;
}
