<?php
namespace Viserio\Contracts\Hashing;

interface Password
{
    /**
     * 1. Hash password using bcrypt-base64-SHA256
     * 2. Encrypt-then-MAC the hash
     *
     * @param string $password
     *
     * @throws \Exception
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function create($password);

    /**
     * 1. VerifyHMAC-then-Decrypt the ciphertext to get the hash
     * 2. Verify that the password matches the hash
     *
     * @param string $password
     * @param string $hashedValue
     *
     * @return bool
     */
    public function verify($password, $hashedValue);
}
