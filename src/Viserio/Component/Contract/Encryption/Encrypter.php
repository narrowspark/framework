<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Encryption;

interface Encrypter
{
    /**
     * Encrypt a message with a key.
     *
     * (Encrypt then MAC -- xsalsa20 then keyed-Blake2b)
     * You don't need to worry about chosen-ciphertext attacks.
     *
     * @param \Viserio\Component\Contract\Encryption\HiddenString $plaintext
     * @param string                                              $additionalData
     * @param string|bool                                         $encoding
     *
     * @return string
     */
    public function encrypt(HiddenString $plaintext, string $additionalData = '', $encoding = Security::ENCODE_BASE64URLSAFE): string;

    /**
     * Decrypt a message with a key.
     *
     * @param string $ciphertext
     * @param string $additionalData
     * @param mixed  $encoding
     *
     * @throws \Viserio\Component\Contract\Encryption\Exception\InvalidMessageException
     *
     * @return \Viserio\Component\Contract\Encryption\HiddenString
     */
    public function decrypt(string $ciphertext, string $additionalData = '', $encoding = Security::ENCODE_BASE64URLSAFE): HiddenString;
}
