<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption;

interface Encrypter
{
    /**
     * Encrypts a plaintext string using a secret key.
     *
     * @param string $plaintext
     *
     * @return string
     */
    public function encrypt(string $plaintext): string;

    /**
     * Decrypts a ciphertext string using a secret key.
     *
     * @param string $ciphertext
     *
     * @return string
     */
    public function decrypt(string $ciphertext): string;

    /**
     * Compare two encrypted values.
     *
     * @param string $encrypted1
     * @param string $encrypted2
     *
     * @return bool
     */
    public function compare(string $encrypted1, string $encrypted2): bool;
}
