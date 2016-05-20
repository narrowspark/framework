<?php
namespace Viserio\Hashing;

use Defuse\Crypto\Key;
use ParagonIE\PasswordLock\PasswordLock;
use Viserio\Contracts\Hashing\Password as PasswordContract;

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
     * @param \Defuse\Crypto\Key $key
     */
    public function __construct(Key $key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function create($password)
    {
        return PasswordLock::hashAndEncrypt($password, $this->key);
    }

    /**
     * {@inheritdoc}
     */
    public function verify($password, $hashedValue)
    {
        return PasswordLock::decryptAndVerify($password, $hashedValue, $this->key);
    }

    /**
     * Key rotation method -- decrypt with your old key then re-encrypt with your new key
     *
     * @param string             $hashedValue
     * @param \Defuse\Crypto\Key $newKey
     *
     * @return bool
     */
    public function shouldRecreate($hashedValue, Key $newKey)
    {
        return PasswordLock::rotateKey($hashedValue, $this->key, $newKey);
    }
}
