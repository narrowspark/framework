<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\Exception\InvalidMessageException;
use Viserio\Component\Contracts\Encryption\HiddenString as HiddenStringContract;
use Viserio\Component\Contracts\Encryption\Password as PasswordContract;
use Viserio\Component\Contracts\Encryption\Security as SecurityContract;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Encryption\Traits\SecurityLevelsTrait;

final class Password implements PasswordContract
{
    use SecurityLevelsTrait;

    /**
     * A encrypter instance.
     *
     * @var \Viserio\Component\Contracts\Encryption\Encrypter
     */
    private $encrypter;

    /**
     * Create a new Password instance.
     *
     * @param \Viserio\Component\Contracts\Encryption\Encrypter $encrypter
     */
    public function __construct(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    /**
     * @param string $stored
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidMessageException
     *
     * @return void
     */
    private static function checkHashLength(string $stored): void
    {
        // Base64-urlsafe encoded, so 4/3 the size of raw binary
        if (\mb_strlen($stored, '8bit') < (SecurityContract::SHORTEST_CIPHERTEXT_LENGTH * 4 / 3)) {
            throw new InvalidMessageException('Encrypted password hash is too short.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hash(
        HiddenStringContract $password,
        string $level = SecurityContract::INTERACTIVE,
        string $additionalData = ''
    ): string {
        $kdfLimits = self::getSecurityLevels($level);

        // First, let's calculate the hash
        $hashed = \sodium_crypto_pwhash_str(
            $password->getString(),
            $kdfLimits[0],
            $kdfLimits[1]
        );

        return $this->encrypter->encrypt(new HiddenString($hashed), $additionalData);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(
        HiddenStringContract $password,
        string $stored,
        string $additionalData = ''
    ): bool {
        // Base64-urlsafe encoded, so 4/3 the size of raw binary
        self::checkHashLength($stored);

        $hash_str = $this->encrypter->decrypt($stored, $additionalData);

        return \sodium_crypto_pwhash_str_verify($hash_str->getString(), $password->getString());
    }

    /**
     * {@inheritdoc}
     */
    public function needsRehash(
        string $stored,
        string $level = SecurityContract::INTERACTIVE,
        string $additionalData = ''
    ): bool {
        self::checkHashLength($stored);

        // First let's decrypt the hash
        $hashInstance = $this->encrypter->decrypt($stored, $additionalData);
        $hashString   = $hashInstance->getString();

        // Upon successful decryption, verify that we're using Argon2i
        if (!\hash_equals(
            \mb_substr($hashString, 0, 9, '8bit'),
            \SODIUM_CRYPTO_PWHASH_STRPREFIX
        )) {
            return true;
        }

        switch ($level) {
            case SecurityContract::INTERACTIVE:
                return !\hash_equals(
                    '$argon2i$v=19$m=32768,t=4,p=1$',
                    \mb_substr($hashString, 0, 30, '8bit')
                );
            case SecurityContract::MODERATE:
                return !\hash_equals(
                    '$argon2i$v=19$m=131072,t=6,p=1$',
                    \mb_substr($hashString, 0, 31, '8bit')
                );
            case SecurityContract::SENSITIVE:
                return !\hash_equals(
                    '$argon2i$v=19$m=524288,t=8,p=1$',
                    \mb_substr($hashString, 0, 31, '8bit')
                );
            default:
                return true;
        }
    }
}
