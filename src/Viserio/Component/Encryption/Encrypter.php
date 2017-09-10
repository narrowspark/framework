<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use RangeException;
use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contract\Encryption\Exception\InvalidMessageException;
use Viserio\Component\Contract\Encryption\HiddenString as HiddenStringContract;
use Viserio\Component\Contract\Encryption\Security as SecurityContract;
use Viserio\Component\Encryption\Traits\ChooseEncoderTrait;

final class Encrypter implements EncrypterContract
{
    use ChooseEncoderTrait;

    /**
     * @var Key
     */
    private $secretKey;

    /**
     * Create a new Encrypter instance.
     *
     * @param \Viserio\Component\Encryption\Key $secretKey
     */
    public function __construct(Key $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(
        HiddenStringContract $plaintext,
        string $additionalData = '',
        $encoding = SecurityContract::ENCODE_BASE64URLSAFE
    ): string {
        // Generate a nonce and HKDF salt:
        $nonce = \random_bytes(\SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $salt  = \random_bytes(SecurityContract::HKDF_SALT_LEN);

        // Split our key into two keys: One for encryption, the other for
        // authentication. By using separate keys, we can reasonably dismiss
        // likely cross-protocol attacks.
        //This uses salted HKDF to split the keys, which is why we need the
        // salt in the first place.
        [$encKey, $authKey] = self::splitKeys($this->secretKey, $salt);

        // Encrypt our message with the encryption key:
        $encrypted = \sodium_crypto_stream_xor(
            $plaintext->getString(),
            $nonce,
            $encKey
        );

        \sodium_memzero($encKey);

        // Calculate an authentication tag:
        $auth = self::calculateMAC(
            SecurityContract::SODIUM_PHP_VERSION . $salt . $nonce . $additionalData . $encrypted,
            $authKey
        );

        \sodium_memzero($authKey);

        $message = SecurityContract::SODIUM_PHP_VERSION . $salt . $nonce . $encrypted . $auth;

        // Wipe every superfluous piece of data from memory
        \sodium_memzero($nonce);
        \sodium_memzero($salt);
        \sodium_memzero($encrypted);
        \sodium_memzero($auth);

        if ($encoder = $this->chooseEncoder($encoding)) {
            return $encoder($message);
        }

        return $message;
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(
        string $ciphertext,
        string $additionalData = '',
        $encoding = SecurityContract::ENCODE_BASE64URLSAFE
    ): HiddenStringContract {
        if ($decoder = $this->chooseEncoder($encoding, true)) {
            // We were given encoded data:
            try {
                $ciphertext = $decoder($ciphertext);
            } catch (RangeException $ex) {
                throw new InvalidMessageException('Invalid character encoding.');
            }
        }

        [$version, $salt, $nonce, $encrypted, $auth] = self::unpackMessageForDecryption($ciphertext);

        // Split our key into two keys: One for encryption, the other for
        // authentication. By using separate keys, we can reasonably dismiss
        // likely cross-protocol attacks.
        // This uses salted HKDF to split the keys, which is why we need the
        // salt in the first place.
        [$encKey, $authKey] = self::splitKeys($this->secretKey, $salt);

        // Check the MAC first
        if (! self::verifyMAC($auth, $version . $salt . $nonce . $additionalData . $encrypted, $authKey)) {
            throw new InvalidMessageException('Invalid message authentication code.');
        }

        \sodium_memzero($salt);
        \sodium_memzero($authKey);

        // sodium_crypto_stream_xor() can be used to encrypt and decrypt
        $plaintext = \sodium_crypto_stream_xor($encrypted, $nonce, $encKey);

        if ($plaintext === false) {
            throw new InvalidMessageException('Invalid message authentication code.');
        }

        \sodium_memzero($encrypted);
        \sodium_memzero($nonce);
        \sodium_memzero($encKey);

        return new HiddenString($plaintext);
    }

    /**
     * Split a key using HKDF-BLAKE2b.
     *
     * @param Key    $master
     * @param string $salt
     *
     * @return string[]
     */
    public static function splitKeys(Key $master, string $salt = ''): array
    {
        $binary = $master->getRawKeyMaterial();

        return [
            \hash_hkdf_blake2b(
                $binary,
                \SODIUM_CRYPTO_SECRETBOX_KEYBYTES,
                SecurityContract::HKDF_SBOX,
                $salt
            ),
            \hash_hkdf_blake2b(
                $binary,
                \SODIUM_CRYPTO_AUTH_KEYBYTES,
                SecurityContract::HKDF_AUTH,
                $salt
            ),
        ];
    }

    /**
     * Unpack a message string into an array (assigned to variables via list()).
     *
     * Should return exactly 6 elements.
     *
     * @param string $ciphertext
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidMessageException
     *
     * @return string[]
     */
    private static function unpackMessageForDecryption(string $ciphertext): array
    {
        $length = \mb_strlen($ciphertext, '8bit');

        // Fail fast on invalid messages
        if ($length < SecurityContract::VERSION_TAG_LEN) {
            throw new InvalidMessageException('Message is too short.');
        }

        // The first 4 bytes are reserved for the version size
        $version = \mb_substr(
            $ciphertext,
            0,
            SecurityContract::VERSION_TAG_LEN,
            '8bit'
        );

        if ($length < SecurityContract::SHORTEST_CIPHERTEXT_LENGTH) {
            throw new InvalidMessageException('Message is too short.');
        }

        // The salt is used for key splitting (via HKDF)
        $salt = \mb_substr(
            $ciphertext,
            SecurityContract::VERSION_TAG_LEN,
            SecurityContract::HKDF_SALT_LEN,
            '8bit'
        );

        // This is the nonce (we authenticated it):
        $nonce = \mb_substr(
            $ciphertext,
            SecurityContract::VERSION_TAG_LEN + SecurityContract::HKDF_SALT_LEN, // 36
            \SODIUM_CRYPTO_STREAM_NONCEBYTES, // 24
            '8bit'
        );

        // This is the sodium_crypto_stream_xor()ed ciphertext
        $encrypted = \mb_substr(
            $ciphertext,
            // 60:
            SecurityContract::VERSION_TAG_LEN + SecurityContract::HKDF_SALT_LEN + \SODIUM_CRYPTO_STREAM_NONCEBYTES,
            // $length - 124
            $length - (
                SecurityContract::VERSION_TAG_LEN +
                SecurityContract::HKDF_SALT_LEN +
                \SODIUM_CRYPTO_STREAM_NONCEBYTES +
                SecurityContract::MAC_BYTE_SIZE
            ),
            '8bit'
        );

        // $auth is the last 32 bytes
        $auth = \mb_substr(
            $ciphertext,
            $length - SecurityContract::MAC_BYTE_SIZE,
            null,
            '8bit'
        );

        // We don't need this anymore.
        \sodium_memzero($ciphertext);

        // Now we return the pieces in a specific order:
        return [$version, $salt, $nonce, $encrypted, $auth];
    }

    /**
     * Verify a Message Authentication Code (MAC) of a message, with a shared
     * key.
     *
     * @param string $mac     Message Authentication Code
     * @param string $message The message to verify
     * @param string $authKey Authentication key (symmetric)
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidMessageException
     *
     * @return bool
     */
    private static function verifyMAC(
        string $mac,
        string $message,
        string $authKey
    ): bool {
        if (\mb_strlen($mac, '8bit') !== SecurityContract::MAC_BYTE_SIZE) {
            throw new InvalidMessageException(
                'Argument 1: Message Authentication Code is not the correct length; is it encoded?'
            );
        }

        $calc = \sodium_crypto_generichash($message, $authKey, SecurityContract::MAC_BYTE_SIZE);
        $res  = \hash_equals($mac, $calc);

        \sodium_memzero($calc);

        return $res;
    }

    /**
     * Calculate a MAC. This is used internally.
     *
     * @param string $message
     * @param string $authKey
     *
     * @return string
     */
    private static function calculateMAC(string $message, string $authKey): string
    {
        return \sodium_crypto_generichash(
            $message,
            $authKey,
            SecurityContract::MAC_BYTE_SIZE
        );
    }
}
