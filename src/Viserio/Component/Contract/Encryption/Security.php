<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Encryption;

interface Security
{
    public const HEADER_VERSION_SIZE        = 4;

    public const SODIUM_PHP_VERSION         = "\x00\x70\x02\x00";
    public const SODIUM_PHP_KEY_VERSION     = "\x01\x70\x02\x00";
    public const SODIUM_PHP_FILE_VERSION    = "\x02\x70\x02\x00";

    public const FILE_BUFFER                = 1048576;

    // For key derivation security levels:
    public const KEY_INTERACTIVE            = 'interactive';

    public const KEY_MODERATE               = 'moderate';

    public const KEY_SENSITIVE              = 'sensitive';

    // All predefined encoder
    public const ENCODE_HEX                 = 'hex';

    public const ENCODE_BASE32              = 'base32';

    public const ENCODE_BASE32HEX           = 'base32hex';

    public const ENCODE_BASE64              = 'base64';

    public const ENCODE_BASE64URLSAFE       = 'base64urlsafe';

    // Options for encryption and decryption
    public const SHORTEST_CIPHERTEXT_LENGTH = 124;

    public const NONCE_BYTES                = \SODIUM_CRYPTO_STREAM_NONCEBYTES;

    public const MAC_ALGO                   = 'BLAKE2b';

    public const MAC_BYTE_SIZE              = \SODIUM_CRYPTO_GENERICHASH_BYTES_MAX;

    public const HKDF_SALT_LEN              = 32;

    public const HKDF_SBOX                  = 'Narrowspark|KeyForEncryption';

    public const HKDF_AUTH                  = 'Narrowspark|KeyForAuthentication';
}
