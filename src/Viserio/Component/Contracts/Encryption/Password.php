<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption;

interface Password
{
    /**
     * 1. Hash password using bcrypt-base64-SHA256
     * 2. Encrypt-then-MAC the hash.
     *
     * @param string $password
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function create(string $password): string;

    /**
     * Decrypt then verify a password
     *
     * @param HiddenString $password    The user's password
     * @param string $stored            The encrypted password hash
     *
     * @throws InvalidMessage
     *
     * @return bool                     Is this password valid?
     */
    public function verify(HiddenString $password, string $hashedValue): bool;
}
