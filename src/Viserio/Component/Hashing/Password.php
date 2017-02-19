<?php
declare(strict_types=1);
namespace Viserio\Component\Hashing;

use Defuse\Crypto\Key;
use ParagonIE\PasswordLock\PasswordLock;
use Viserio\Component\Contracts\Hashing\Password as PasswordContract;

class Password implements PasswordContract
{
    /**
     * Encryption key.
     *
     * @var \Defuse\Crypto\Key
     */
    protected $key;

    /**
     * Create a new Password instance.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = Key::loadFromAsciiSafeString($key);
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $password): string
    {
        return PasswordLock::hashAndEncrypt($password, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $password, string $hashedValue): bool
    {
        return PasswordLock::decryptAndVerify($password, $hashedValue, $this->key);
    }

    /**
     * Key rotation method -- decrypt with your old key then re-encrypt with your new key.
     *
     * @param string $hashedValue
     * @param string $newKey
     *
     * @return string
     */
    public function shouldRecreate(string $hashedValue, string $newKey): string
    {
        return PasswordLock::rotateKey($hashedValue, $this->key, Key::loadFromAsciiSafeString($newKey));
    }
}
