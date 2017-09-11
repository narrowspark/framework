<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Encryption;

use Viserio\Component\Contract\Encryption\Exception\InvalidMessageException;
use Viserio\Component\Contract\Encryption\Security as SecurityContract;
use Viserio\Component\Contract\Filesystem\Exception\FileModifiedException;
use Viserio\Component\Encryption\HiddenString;
use Viserio\Component\Encryption\Key;
use Viserio\Component\Filesystem\Stream\MutableFile;
use Viserio\Component\Filesystem\Stream\ReadOnlyFile;

final class File
{
    /**
     * @var \Viserio\Component\Encryption\Key
     */
    private $key;

    /**
     * @param \Viserio\Component\Encryption\Key $key
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }

    /**
     * Encrypt a file using key encryption.
     *
     * @param string|resource $input  File name or file handle
     * @param string|resource $output File name or file handle
     *
     * @throws InvalidType
     *
     * @return int Number of bytes written
     */
    public function encrypt($input, $output)
    {
        if ((\is_resource($input) || \is_string($input)) &&
            (\is_resource($output) || \is_string($output))
        ) {
            $readOnly = new ReadOnlyFile($input);
            $mutable  = new MutableFile($output);

            $data = $this->encryptData(
                $readOnly,
                $mutable
            );

            $readOnly->close();
            $mutable->close();

            return $data;
        }
    }

    /**
     * Decrypt a file using key encryption.
     *
     * @param string|resource $input  File name or file handle
     * @param string|resource $output File name or file handle
     *
     * @throws InvalidType
     *
     * @return bool TRUE if successful
     */
    public function decrypt($input, $output): bool
    {
        if ((\is_resource($input) || \is_string($input))
            &&
            (\is_resource($output) || \is_string($output))
        ) {
            try {
                $readOnly = new ReadOnlyFile($input);
                $mutable = new MutableFile($output);
                $data = $this->decryptData($readOnly, $mutable);

                return $data;
            } finally {
                $readOnly->close();
                $mutable->close();
            }
        }
    }

    /**
     * Encrypt the contents of a file.
     *
     * @param $input
     * @param $output
     *
     * @return int
     */
    private function encryptData(ReadOnlyFile $input, MutableFile $output): int
    {
        // Generate a nonce and HKDF salt
        $firstNonce         = \random_bytes(SecurityContract::NONCE_BYTES);
        $hkdfSalt           = \random_bytes(SecurityContract::HKDF_SALT_LEN);
        [$encKey, $authKey] = $this->splitKeys($this->key, $hkdfSalt);

        // Write the header
        $output->write(
            SecurityContract::SODIUM_PHP_VERSION,
            SecurityContract::HEADER_VERSION_SIZE
        );
        $output->write($firstNonce, \SODIUM_CRYPTO_STREAM_NONCEBYTES);
        $output->write($hkdfSalt, SecurityContract::HKDF_SALT_LEN);

        // BMAC
        $mac = \sodium_crypto_generichash_init($authKey);
        \sodium_crypto_generichash_update($mac, SecurityContract::SODIUM_PHP_VERSION);
        \sodium_crypto_generichash_update($mac, $firstNonce);
        \sodium_crypto_generichash_update($mac, $hkdfSalt);
        \sodium_memzero($authKey);
        \sodium_memzero($hkdfSalt);

        return $this->streamEncrypt(
            $input,
            $output,
            new Key(new HiddenString($encKey)),
            $firstNonce,
            $mac
        );
    }

    /**
     * Stream encryption.
     *
     * @param ReadOnlyFile                      $input
     * @param MutableFile                       $output
     * @param \Viserio\Component\Encryption\Key $encKey
     * @param string                            $nonce
     * @param string                            $mac    (hash context for BLAKE2b)
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\FileModifiedException
     *
     * @return int (number of bytes)
     */
    private static function streamEncrypt(
        ReadOnlyFile $input,
        MutableFile $output,
        Key $encKey,
        string $nonce,
        string $mac
    ): int {
        $initHash = $input->getHash();
        // Begin the streaming decryption
        $size    = $input->getSize();
        $written = 0;

        while ($input->getRemainingBytes() > 0) {
            $read = $input->read(
                ($input->tell() + SecurityContract::FILE_BUFFER) > $size ?
                    ($size - $input->tell()) :
                    SecurityContract::FILE_BUFFER
            );
            $encrypted = \sodium_crypto_stream_xor(
                $read,
                $nonce,
                $encKey->getRawKeyMaterial()
            );

            \sodium_crypto_generichash_update($mac, $encrypted);

            $written += $output->write($encrypted);

            \sodium_increment($nonce);
        }

        \sodium_memzero($nonce);

        // Check that our input file was not modified before we MAC it
        if (! \hash_equals($input->getHash(), $initHash)) {
            throw new FileModifiedException(
                'Read-only file has been modified since it was opened for reading.'
            );
        }

        $mac = \sodium_crypto_generichash_final($mac, SecurityContract::MAC_BYTE_SIZE);
        $written += $output->write($mac, SecurityContract::MAC_BYTE_SIZE);

        \sodium_memzero($mac);

        return $written;
    }

    /**
     * Decrypt the contents of a file.
     *
     * @param $input
     * @param $output
     *
     * @throws InvalidMessageException
     *
     * @return bool
     */
    private function decryptData(ReadOnlyFile $input, MutableFile $output): bool
    {
        $input->seek(0);
        // Make sure it's large enough to even read a version tag
        if ($input->getSize() < SecurityContract::HEADER_VERSION_SIZE) {
            throw new InvalidMessageException(
                "Input file is too small to have been encrypted."
            );
        }
        // Parse the header, ensuring we get 4 bytes
        $header = $input->read(SecurityContract::HEADER_VERSION_SIZE);

        // Is this shorter than an encrypted empty string?
        if ($input->getSize() < SecurityContract::SHORTEST_CIPHERTEXT_LENGTH) {
            throw new InvalidMessageException(
                "Input file is too small to have been encrypted."
            );
        }
        // Let's grab the first nonce and salt
        $firstNonce = $input->read(SecurityContract::NONCE_BYTES);
        $hkdfSalt = $input->read(SecurityContract::HKDF_SALT_LEN);
        // Split our keys, begin the HMAC instance
        [$encKey, $authKey] = $this->splitKeys($this->key, $hkdfSalt);

        // BMAC
        $mac = \sodium_crypto_generichash_init($authKey);
        \sodium_crypto_generichash_update($mac, $header);
        \sodium_crypto_generichash_update($mac, $firstNonce);
        \sodium_crypto_generichash_update($mac, $hkdfSalt);
        \sodium_memzero($authKey);
        \sodium_memzero($hkdfSalt);

        $ret = $this->streamDecrypt(
            $input,
            $output,
            new Key(new HiddenString($encKey)),
            $firstNonce,
            $mac,
            $this->streamVerify($input, safe_str_cpy($mac))
        );

        \sodium_memzero($encKey);
        unset($encKey);
        unset($authKey);
        unset($firstNonce);
        unset($mac);
        unset($config);

        return $ret;
    }

    /**
     * Split a key using HKDF-BLAKE2b.
     *
     * @param Key    $masterKey
     * @param string $salt
     *
     * @return array<int, string>
     */
    private static function splitKeys(Key $masterKey, string $salt): array
    {
        $binary = $masterKey->getRawKeyMaterial();

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
     * Recalculate and verify the HMAC of the input file.
     *
     * @param ReadOnlyFile $input  The file we are verifying
     * @param resource|string $mac (hash context)
     *
     * @return array               Hashes of various chunks
     *
     * @throws CannotPerformOperation
     * @throws InvalidMessage
     */
    final private static function streamVerify(ReadOnlyFile $input, $mac): array
    {

    }
}
