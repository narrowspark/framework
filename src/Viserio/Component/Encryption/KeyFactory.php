<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Error;
use ParagonIE\ConstantTime\Hex;
use Viserio\Component\Contract\Encryption\Exception\CannotPerformOperationException;
use Viserio\Component\Contract\Encryption\Exception\InvalidKeyException;
use Viserio\Component\Contract\Encryption\Exception\InvalidSaltException;
use Viserio\Component\Contract\Encryption\HiddenString as HiddenStringContract;
use Viserio\Component\Contract\Encryption\Security as SecurityContract;
use Viserio\Component\Encryption\Traits\SecurityLevelsTrait;

final class KeyFactory
{
    use SecurityLevelsTrait;

    /**
     * Don't allow this to be instantiated.
     *
     * @throws \Error
     */
    private function __construct()
    {
        throw new Error('Do not instantiate.');
    }

    /**
     * Generate an an encryption key (symmetric-key cryptography).
     *
     * @param string &$secretKey
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidKeyException
     *
     * @return \Viserio\Component\Encryption\Key
     */
    public static function generateKey(string &$secretKey = ''): Key
    {
        $secretKey = \random_bytes(\SODIUM_CRYPTO_STREAM_KEYBYTES);

        return new Key(new HiddenString($secretKey));
    }

    /**
     * Derive an encryption key (symmetric-key cryptography) from a password
     * and salt.
     *
     * @param \Viserio\Component\Contract\Encryption\HiddenString $password
     * @param string                                              $salt
     * @param string                                              $level    Security level for KDF
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidSaltException
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidTypeException
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidKeyException
     *
     * @return \Viserio\Component\Encryption\Key
     */
    public static function deriveKey(
        HiddenStringContract $password,
        string $salt,
        string $level = SecurityContract::KEY_INTERACTIVE
    ): Key {
        $kdfLimits = self::getSecurityLevels($level);

        // VERSION 2+ (argon2)
        if (\mb_strlen($salt, '8bit') !== \SODIUM_CRYPTO_PWHASH_SALTBYTES) {
            throw new InvalidSaltException(sprintf(
                'Expected %s bytes, got %s.',
                \SODIUM_CRYPTO_PWHASH_SALTBYTES,
                \mb_strlen($salt, '8bit')
            ));
        }

        $secretKey = \sodium_crypto_pwhash(
            \SODIUM_CRYPTO_STREAM_KEYBYTES,
            $password->getString(),
            $salt,
            $kdfLimits[0],
            $kdfLimits[1]
        );

        return new Key(new HiddenString($secretKey));
    }

    /**
     * Load a symmetric encryption key from a string.
     *
     * @param \Viserio\Component\Encryption\HiddenString $keyData
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidKeyException
     *
     * @return \Viserio\Component\Encryption\Key
     */
    public static function importFromHiddenString(HiddenString $keyData): Key
    {
        return new Key(
            new HiddenString(
                self::getKeyDataFromString(
                    Hex::decode($keyData->getString())
                )
            )
        );
    }

    /**
     * Export a cryptography key to a string (with a checksum).
     *
     * @param \Viserio\Component\Encryption\Key $key
     *
     * @return \Viserio\Component\Encryption\HiddenString
     */
    public static function exportToHiddenString(Key $key): HiddenString
    {
        return new HiddenString(self::convertToHexadecimalString($key));
    }

    /**
     * Save a key to a file.
     *
     * @param string                            $filePath
     * @param \Viserio\Component\Encryption\Key $key
     *
     * @return bool
     */
    public static function saveKeyToFile(string $filePath, Key $key): bool
    {
        $saved = \file_put_contents(
            $filePath,
            self::convertToHexadecimalString($key)
        );

        return $saved !== false;
    }

    /**
     * Load a symmetric encryption key from a file.
     *
     * @param string $filePath
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\CannotPerformOperationException
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidKeyException
     *
     * @return \Viserio\Component\Encryption\Key
     */
    public static function loadKey(string $filePath): Key
    {
        if (! \is_readable($filePath)) {
            throw new CannotPerformOperationException(sprintf(
                'Cannot read keyfile: %s',
                $filePath
            ));
        }

        return new Key(self::loadKeyFile($filePath));
    }

    /**
     * Read a key from a file, verify its checksum.
     *
     * @param string $filePath
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\CannotPerformOperationException
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidKeyException
     *
     * @return \Viserio\Component\Contract\Encryption\HiddenString
     */
    private static function loadKeyFile(string $filePath): HiddenStringContract
    {
        $fileData = \file_get_contents($filePath);

        if ($fileData === false) {
            throw new CannotPerformOperationException(sprintf(
                'Cannot load key from file: %s',
                $filePath
            ));
        }

        $data = Hex::decode($fileData);

        \sodium_memzero($fileData);

        return new HiddenString(self::getKeyDataFromString($data));
    }

    /**
     * Convert a binary string into a hexadecimal string without cache-timing
     * leaks.
     *
     * @param \Viserio\Component\Encryption\Key $key
     *
     * @return string
     */
    private static function convertToHexadecimalString(Key $key): string
    {
        return Hex::encode(
            SecurityContract::SODIUM_PHP_KEY_VERSION . $key->getRawKeyMaterial() .
            \sodium_crypto_generichash(
                SecurityContract::SODIUM_PHP_KEY_VERSION . $key->getRawKeyMaterial(),
                '',
                \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX
            )
        );
    }

    /**
     * Take a stored key string, get the derived key (after verifying the checksum).
     *
     * @param string $data
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidKeyException
     *
     * @return string
     */
    private static function getKeyDataFromString(string $data): string
    {
        $version = \mb_substr($data, 0, SecurityContract::HEADER_VERSION_SIZE, '8bit');
        $keyData = \mb_substr(
            $data,
            SecurityContract::HEADER_VERSION_SIZE,
            -\SODIUM_CRYPTO_GENERICHASH_BYTES_MAX,
            '8bit'
        );
        $checksum = \mb_substr(
            $data,
            -\SODIUM_CRYPTO_GENERICHASH_BYTES_MAX,
            \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX,
            '8bit'
        );
        $calc    = \sodium_crypto_generichash(
            $version . $keyData,
            '',
            \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX
        );

        if (! \hash_equals($calc, $checksum)) {
            throw new InvalidKeyException('Checksum validation fail.');
        }

        \sodium_memzero($data);
        \sodium_memzero($calc);
        \sodium_memzero($version);
        \sodium_memzero($checksum);

        return $keyData;
    }
}
