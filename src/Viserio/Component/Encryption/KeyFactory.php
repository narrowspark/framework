<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use ParagonIE\ConstantTime\Hex;
use Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperationException;
use Viserio\Component\Contracts\Encryption\Exception\InvalidKeyException;
use Viserio\Component\Contracts\Encryption\Exception\InvalidSaltException;
use Viserio\Component\Contracts\Encryption\HiddenString as HiddenStringContract;
use Viserio\Component\Contracts\Encryption\Security as SecurityContract;
use Viserio\Component\Encryption\Traits\SecurityLevelsTrait;

final class KeyFactory
{
    use SecurityLevelsTrait;

    /**
     * Generate an an encryption key (symmetric-key cryptography).
     *
     * @param string &$secretKey
     *
     * @return \Viserio\Component\Encryption\Key
     */
    public static function generateKey(string &$secretKey = ''): Key
    {
        $secretKey = \random_bytes(SODIUM_CRYPTO_STREAM_KEYBYTES);

        return new Key(new HiddenString($secretKey));
    }

    /**
     * Derive an encryption key (symmetric-key cryptography) from a password
     * and salt.
     *
     * @param \Viserio\Component\Contracts\Encryption\HiddenString $password
     * @param string                                               $salt
     * @param string                                               $level    Security level for KDF
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidSaltException
     *
     * @return \Viserio\Component\Encryption\Key
     */
    public static function deriveKey(
        HiddenStringContract $password,
        string $salt,
        string $level = SecurityContract::INTERACTIVE
    ): Key {
        $kdfLimits = self::getSecurityLevels($level);

        // VERSION 2+ (argon2)
        if (\mb_strlen($salt, '8bit') !== SODIUM_CRYPTO_PWHASH_SALTBYTES) {
            throw new InvalidSaltException(sprintf(
                'Expected %s bytes, got %s.',
                SODIUM_CRYPTO_PWHASH_SALTBYTES,
                \mb_strlen($salt, '8bit')
            ));
        }

        $secretKey = \sodium_crypto_pwhash(
            SODIUM_CRYPTO_STREAM_KEYBYTES,
            $password->getString(),
            $salt,
            $kdfLimits[0],
            $kdfLimits[1]
        );

        return new Key(new HiddenString($secretKey));
    }

    /**
     * Load a symmetric key from a file.
     *
     * @param string $filePath
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperationException
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
     * Save a key to a file.
     *
     * @param string $filePath
     * @param string $keyData
     *
     * @return bool
     */
    public static function saveKeyFile(string $filePath, string $keyData): bool
    {
        $saved = \file_put_contents(
            $filePath,
            Hex::encode(
                SecurityContract::SODIUM_PHP_VERSION . $keyData .
                \sodium_crypto_generichash(
                    SecurityContract::SODIUM_PHP_VERSION . $keyData,
                    '',
                    SODIUM_CRYPTO_GENERICHASH_BYTES_MAX
                )
            )
        );

        return $saved !== false;
    }

    /**
     * Take a stored key string, get the derived key (after verifying the
     * checksum).
     *
     * @param string $data
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidKeyException
     *
     * @return string
     */
    private static function getKeyDataFromString(string $data): string
    {
        $version  = \mb_substr($data, 0, 4, '8bit');
        $keyData  = \mb_substr(
            $data,
            4,
            -SODIUM_CRYPTO_GENERICHASH_BYTES_MAX,
            '8bit'
        );
        $checksum = \mb_substr(
            $data,
            -SODIUM_CRYPTO_GENERICHASH_BYTES_MAX,
            SODIUM_CRYPTO_GENERICHASH_BYTES_MAX,
            '8bit'
        );
        $calc    = \sodium_crypto_generichash(
            $version. $keyData,
            '',
            SODIUM_CRYPTO_GENERICHASH_BYTES_MAX
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

    /**
     * Read a key from a file, verify its checksum.
     *
     * @param string $filePath
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperationException
     *
     * @return HiddenString
     */
    protected static function loadKeyFile(string $filePath): HiddenString
    {
        $fileData = \file_get_contents($filePath);

        if ($fileData === false) {
            throw new CannotPerformOperationException(sprintf(
                'Cannot load key from file: %s.',
                $filePath
            ));
        }

        $data = Hex::decode($fileData);

        \sodium_memzero($fileData);

        return new HiddenString(self::getKeyDataFromString($data));
    }
}
