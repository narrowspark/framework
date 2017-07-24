<?php
declare(strict_types=1);
namespace Viserio\Component\Exception;

use Ramsey\Uuid\Uuid;
use Throwable;

class ExceptionIdentifier
{
    /**
     * The identification mappings.
     *
     * Note that for performance reasons, we only store up to 16 identifications in
     * memory at a given time.
     *
     * @var string[]
     */
    protected $identification;

    /**
     * Identify the given exception.
     *
     * @param \Throwable $exception
     *
     * @throws \Ramsey\Uuid\Exception\UnsatisfiedDependencyException
     *
     * @return string
     */
    public function identify(Throwable $exception): string
    {
        $hash = \spl_object_hash($exception);

        // if we know about the exception, return it's id
        if (isset($this->identification[$hash])) {
            return $this->identification[$hash];
        }

        // cleanup in preparation for the identification
        if (\count((array) $this->identification) >= 16) {
            \array_shift($this->identification);
        }

        // generate, store, and return the id
        return $this->identification[$hash] = $this->uuid4();
    }

    /**
     * Generate v4 UUID.
     *
     * We're generating uuids according to the official v4 spec.
     *
     * @return string
     */
    private function uuid4(): string
    {
        $hash   = \bin2hex(\random_bytes(16));
        $timeHi = \hexdec(\mb_substr($hash, 12, 4)) & 0x0fff;
        $timeHi &= ~0xf000;
        $timeHi |= 4 << 12;

        $clockSeqHi = \hexdec(\mb_substr($hash, 16, 2)) & 0x3f;
        $clockSeqHi &= ~0xc0;
        $clockSeqHi |= 0x80;

        $params = [\mb_substr($hash, 0, 8), \mb_substr($hash, 8, 4), \sprintf('%04x', $timeHi), \sprintf('%02x', $clockSeqHi), \mb_substr($hash, 18, 2), \mb_substr($hash, 20, 12)];

        return \vsprintf('%08s-%04s-%04s-%02s%02s-%012s', $params);
    }
}
