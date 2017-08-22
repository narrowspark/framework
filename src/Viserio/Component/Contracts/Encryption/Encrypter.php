<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption;

interface Encrypter
{
    /**
     * Encrypt a message using the Halite encryption protocol.
     *
     * (Encrypt then MAC -- xsalsa20 then keyed-Blake2b)
     * You don't need to worry about chosen-ciphertext attacks.
     *
     * @param \Viserio\Component\Contracts\Encryption\HiddenString $plaintext
     * @param string                                               $additionalData
     * @param string|bool                                          $encoding
     *
     * @return string
     */
    public function encrypt(HiddenString $plaintext, string $additionalData = '', $encoding = Security::ENCODE_BASE64URLSAFE): string;

    /**
     * Decrypt a message using the Halite encryption protocol.
     *
     * @param string $ciphertext
     * @param string $additionalData
     * @param mixed  $encoding
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidMessageException
     *
     * @return \Viserio\Component\Contracts\Encryption\HiddenString
     */
    public function decrypt(string $ciphertext, string $additionalData = '', $encoding = Security::ENCODE_BASE64URLSAFE): HiddenString;
}
