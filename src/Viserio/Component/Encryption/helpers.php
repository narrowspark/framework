<?php
declare(strict_types=1);

use ParagonIE\ConstantTime\Hex;
use Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperationException;
use Viserio\Component\Contracts\Encryption\Exception\InvalidLengthException;

if (! \function_exists('safe_str_cpy')) {
    /**
     * PHP 7 uses interned strings. We don't want altering this one to alter
     * the original string.
     *
     * @param string $string
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperationException
     *
     * @return string
     */
    function safe_str_cpy(string $string): string
    {
        $length = \mb_strlen($string, '8bit');

        if ($length === false) {
            throw new CannotPerformOperationException('mb_strlen() failed unexpectedly.');
        }

        $return = '';

        for ($i = 0; $i < $length; ++$i) {
            $return .= $string[$i];
        }

        return $return;
    }
}

if (! \function_exists('raw_keyed_hash')) {
    /**
     * Wrapper around \sodium_crypto_generichash().
     *
     * Expects a key (binary string).
     *
     * @param string $input
     * @param string $key
     * @param int    $length
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperationException
     *
     * @return string Returns raw binary.
     */
    function raw_keyed_hash(string $input, string $key, int $length = SODIUM_CRYPTO_GENERICHASH_BYTES): string
    {
        if ($length < SODIUM_CRYPTO_GENERICHASH_BYTES_MIN) {
            throw new CannotPerformOperationException(
                \sprintf(
                    'Output length must be at least %d bytes.',
                    SODIUM_CRYPTO_GENERICHASH_BYTES_MIN
                )
            );
        }

        if ($length > SODIUM_CRYPTO_GENERICHASH_BYTES_MAX) {
            throw new CannotPerformOperationException(
                \sprintf(
                    'Output length must be at most %d bytes.',
                    SODIUM_CRYPTO_GENERICHASH_BYTES_MAX
                )
            );
        }

        return \sodium_crypto_generichash($input, $key, $length);
    }
}

if (! \function_exists('keyed_hash')) {
    /**
     * Wrapper around \sodium_crypto_generichash().
     *
     * Expects a key (binary string).
     *
     * @param string $input
     * @param string $key
     * @param int    $length
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperationException
     *
     * @return string Returns hexadecimal characters.
     */
    function keyed_hash(string $input, string $key, int $length = SODIUM_CRYPTO_GENERICHASH_BYTES): string
    {
        return Hex::encode(raw_keyed_hash($input, $key, $length));
    }
}

if (! \function_exists('hash_hkdf_blake2b')) {
    /**
     * Use a derivative of HKDF to derive multiple keys from one.
     * http://tools.ietf.org/html/rfc5869.
     *
     * This is a variant from hash_hkdf() and instead uses BLAKE2b provided by
     * libsodium.
     *
     * Important: instead of a true HKDF (from HMAC) construct, this uses the
     * sodium_crypto_generichash() key parameter. This is *probably* okay.
     *
     * @param string $ikm    Initial Keying Material
     * @param int    $length How many bytes?
     * @param string $info   What sort of key are we deriving?
     * @param string $salt
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotPerformOperationException
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidLengthException
     *
     * @return string
     */
    function hash_hkdf_blake2b(string $ikm, int $length, string $info = '', string $salt = ''): string
    {
        // Sanity-check the desired output length.
        if ($length < 0 || $length > (255 * SODIUM_CRYPTO_GENERICHASH_KEYBYTES)) {
            throw new InvalidLengthException('Argument 2: Bad HKDF Digest Length.');
        }

        // "If [salt] not provided, is set to a string of HashLen zeroes."
        if (empty($salt)) {
            $salt = \str_repeat("\x00", SODIUM_CRYPTO_GENERICHASH_KEYBYTES);
        }

        // HKDF-Extract:
        // PRK = HMAC-Hash(salt, IKM)
        // The salt is the HMAC key.
        $prk = \raw_keyed_hash($ikm, $salt);

        // HKDF-Expand:
        // This check is useless, but it serves as a reminder to the spec.
        if (\mb_strlen($prk, '8bit') < SODIUM_CRYPTO_GENERICHASH_KEYBYTES) {
            throw new CannotPerformOperationException('An unknown error has occurred.');
        }

        // T(0) = ''
        $t          = '';
        $last_block = '';

        for ($block_index = 1; \mb_strlen($t, '8bit') < $length; ++$block_index) {
            // T(i) = HMAC-Hash(PRK, T(i-1) | info | 0x??)
            $last_block = \raw_keyed_hash(
                $last_block . $info . \chr($block_index),
                $prk
            );
            // T = T(1) | T(2) | T(3) | ... | T(N)
            $t .= $last_block;
        }

        // ORM = first L octets of T
        $orm = mb_substr($t, 0, $length, '8bit');

        if ($orm === false) {
            throw new CannotPerformOperationException('An unknown error has occurred.');
        }

        return $orm;
    }
}
