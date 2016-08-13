<?php
declare(strict_types=1);
namespace Viserio\Contracts\Http\Exceptions;

use RuntimeException;
use Throwable;

class ByteCountingStreamException extends RuntimeException
{
    /**
     * Expect number of bytes to be read.
     *
     * @var int
     */
    private $expectBytes;

    /**
     * Actual number of bytes remaining.
     *
     * @var int
     */
    private $actualBytes;

    /**
     * ByteCountingStreamException constructor.
     *
     * @param int             $expect   expected bytes to be read
     * @param int             $actual   actual available bytes to read
     * @param \Throwable|null $previous Exception being thrown
     */
    public function __construct(int $expect, int $actual, Throwable $previous = null)
    {
        $msg = 'The ByteCountingStream decorator expects to be able to '
        . sprintf('read %s bytes from a stream, but the stream being decorated ', $expect)
        . sprintf('only contains %s bytes.', $actual);

        $this->expectBytes = $expect;
        $this->actualBytes = $actual;

        parent::__construct($msg, 0, $previous);
    }

    /**
     * Get expected bytes to be read.
     *
     * @return int
     */
    public function getExpectBytes(): int
    {
        return $this->expectBytes;
    }

    /**
     * Get remaining bytes available for read.
     *
     * @return int
     */
    public function getRemainingBytes(): int
    {
        return $this->actualBytes;
    }
}
