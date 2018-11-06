<?php
declare(strict_types=1);
namespace Viserio\Component\Exception;

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
    private static $identification = [];

    /**
     * Identify the given exception.
     *
     * @param \Throwable $exception
     *
     * @return string
     */
    public static function identify(Throwable $exception): string
    {
        $hash = \spl_object_hash($exception);

        // if we know about the exception, return it's id
        if (isset(self::$identification[$hash])) {
            return self::$identification[$hash];
        }

        // cleanup in preparation for the identification
        if (\count((array) self::$identification) >= 16) {
            \array_shift(self::$identification);
        }

        // generate, store, and return the id
        return self::$identification[$hash] = self::uuid4();
    }

    /**
     * Generate v4 UUID.
     *
     * We're generating uuids according to the official v4 spec.
     *
     * @return string
     */
    private static function uuid4(): string
    {
        $hash   = \bin2hex(\random_bytes(16));
        $timeHi = \hexdec(\substr($hash, 12, 4)) & 0x0fff;
        $timeHi &= ~0xf000;
        $timeHi |= 4 << 12;

        $clockSeqHi = \hexdec(\substr($hash, 16, 2)) & 0x3f;
        $clockSeqHi &= ~0xc0;
        $clockSeqHi |= 0x80;

        $params = [\substr($hash, 0, 8), \substr($hash, 8, 4), \sprintf('%04x', $timeHi), \sprintf('%02x', $clockSeqHi), \substr($hash, 18, 2), \substr($hash, 20, 12)];

        return \vsprintf('%08s-%04s-%04s-%02s%02s-%012s', $params);
    }
}
