<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption;

interface Encrypter
{
    /**
     * Encrypt a message using the Halite encryption protocol
     *
     * (Encrypt then MAC -- xsalsa20 then keyed-Blake2b)
     * You don't need to worry about chosen-ciphertext attacks.
     *
     * @param Viserio\Component\Contracts\Encryption\HiddenString $plaintext
     * @param string|bool  $encoding
     *
     * @return string
     */
    public function encrypt(HiddenString $plaintext, $encoding = SecurityContract::ENCODE_BASE64URLSAFE): string;

    /**
     * Decrypt a message using the Halite encryption protocol
     *
     * @param string $ciphertext
     * @param mixed  $encoding
     *
     * @return \Viserio\Component\Contracts\Encryption\HiddenString
     *
     * @throws InvalidMessage
     */
    public function decrypt(string $ciphertext, $encoding = Security::ENCODE_BASE64URLSAFE): HiddenString;
}
