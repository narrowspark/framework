<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption;

interface Password
{
    /**
     * Hash then encrypt a password.
     *
     * @param \Viserio\Component\Contracts\Encryption\HiddenString $password       The user's password
     * @param string                                               $level          The security level for this password
     * @param string                                               $additionalData Additional authenticated data
     *
     * @return string An encrypted hash to store
     */
    public function hash(
        HiddenString $password,
        string $level = Security::INTERACTIVE,
        string $additionalData = ''
    ): string;

    /**
     * Decrypt then verify a password.
     *
     * @param \Viserio\Component\Contracts\Encryption\HiddenString $password       The user's password
     * @param string                                               $stored         The encrypted password hash
     * @param string                                               $additionalData Additional authenticated data (if used to encrypt, mandatory)
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidLengthException
     *
     * @return bool Is this password valid?
     */
    public function verify(
        HiddenString $password,
        string $stored,
        string $additionalData = ''
    ): bool;

    /**
     * Is this password hash stale?
     *
     * @param string $stored         Encrypted password hash
     * @param string $level          The security level for this password
     * @param string $additionalData Additional authenticated data (needed to decrypt)
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidLengthException
     *
     * @return bool Do we need to regenerate the hash or ciphertext?
     */
    public function needsRehash(
        string $stored,
        string $level = Security::INTERACTIVE,
        string $additionalData = ''
    ): bool;
}
