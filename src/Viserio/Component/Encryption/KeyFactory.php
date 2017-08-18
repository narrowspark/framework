<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\Exception\InvalidType;
use Viserio\Component\Contracts\Encryption\Security as SecurityContract;

final class KeyFactory
{
    /**
     * Returns a 2D array [OPSLIMIT, MEMLIMIT] for the appropriate security level.
     *
     * @param string $level
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidType
     *
     * @return int[]
     */
    public static function getSecurityLevels(string $level = SecurityContract::INTERACTIVE): array
    {
        switch ($level) {
            case self::INTERACTIVE:
                return [
                    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
                    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE,
                ];
            case self::MODERATE:
                return [
                    SODIUM_CRYPTO_PWHASH_OPSLIMIT_MODERATE,
                    SODIUM_CRYPTO_PWHASH_MEMLIMIT_MODERATE,
                ];
            case self::SENSITIVE:
                return [
                    SODIUM_CRYPTO_PWHASH_OPSLIMIT_SENSITIVE,
                    SODIUM_CRYPTO_PWHASH_MEMLIMIT_SENSITIVE,
                ];
            default:
                throw new InvalidType('Invalid security level for Argon2i.');
        }
    }
}
