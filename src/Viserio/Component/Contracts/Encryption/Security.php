<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption;

interface Security
{
    public const SODIUM_PHP_VERSION         = "\x00\x70\x02\x00";

    public const VERSION_TAG_LEN            = 4;

    // For key derivation security levels:
    public const INTERACTIVE                = 'interactive';

    public const MODERATE                   = 'moderate';

    public const SENSITIVE                  = 'sensitive';

    public const ENCODE_HEX                 = 'hex';

    public const ENCODE_BASE32              = 'base32';

    public const ENCODE_BASE32HEX           = 'base32hex';

    public const ENCODE_BASE64              = 'base64';

    public const ENCODE_BASE64URLSAFE       = 'base64urlsafe';

    public const SHORTEST_CIPHERTEXT_LENGTH = 124;

    public const NONCE_BYTES                = \SODIUM_CRYPTO_STREAM_NONCEBYTES;

    public const HKDF_SALT_LEN              = 32;

    public const MAC_ALGO                   = 'BLAKE2b';

    public const MAC_SIZE                   = \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX;

    public const HKDF_SBOX                  = 'Narrowspark|EncryptionKey';

    public const HKDF_AUTH                  = 'AuthenticationKeyFor_|Narrowspark';
}
