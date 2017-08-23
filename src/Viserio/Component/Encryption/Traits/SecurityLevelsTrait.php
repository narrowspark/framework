<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Traits;

use Viserio\Component\Contracts\Encryption\Exception\InvalidTypeException;
use Viserio\Component\Contracts\Encryption\Security as SecurityContract;

trait SecurityLevelsTrait
{
    /**
     * Returns a 2D array [OPSLIMIT, MEMLIMIT] for the appropriate security level.
     *
     * @param string $level
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidTypeException
     *
     * @return int[]
     */
    public static function getSecurityLevels(string $level = SecurityContract::KEY_INTERACTIVE): array
    {
        switch ($level) {
            case SecurityContract::KEY_INTERACTIVE:
                return [
                    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
                ];
            case SecurityContract::KEY_MODERATE:
                return [
                    SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE,
                    SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE,
                ];
            case SecurityContract::KEY_SENSITIVE:
                return [
                    SODIUM_CRYPTO_PWHASH_OPSLIMIT_SENSITIVE,
                    SODIUM_CRYPTO_PWHASH_MEMLIMIT_SENSITIVE,
                ];
            default:
                throw new InvalidTypeException('Invalid security level for Argon2i.');
        }
    }
}
