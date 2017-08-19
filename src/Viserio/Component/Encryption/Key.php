<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\Exception\CannotCloneKeyException;
use Viserio\Component\Contracts\Encryption\Exception\CannotSerializeKeyException;
use Viserio\Component\Contracts\Encryption\Exception\InvalidKeyException;
use Viserio\Component\Contracts\Encryption\HiddenString as HiddenStringContract;

final class Key
{
    /**
     * @var string
     */
    private $keyMaterial;

    /**
     * You probably should not be using this directly.
     *
     * @param \Viserio\Component\Contracts\Encryption\HiddenString $keyMaterial - The actual key data
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\InvalidKeyException
     */
    public function __construct(HiddenStringContract $keyMaterial)
    {
        $key = safe_str_cpy($keyMaterial->getString());

        if (mb_strlen($key, '8bit') !== SODIUM_CRYPTO_STREAM_KEYBYTES) {
            throw new InvalidKeyException('Encryption key must be SODIUM_CRYPTO_STREAM_KEYBYTES bytes long.');
        }

        $this->keyMaterial = $key;
    }

    /**
     * Make sure you wipe the key from memory on destruction.
     */
    public function __destruct()
    {
        \sodium_memzero($this->keyMaterial);

        $this->keyMaterial = null;
    }

    /**
     * Don't let this ever succeed.
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotCloneKeyException
     */
    public function __clone()
    {
        throw new CannotCloneKeyException();
    }

    /**
     * Don't allow this object to ever be serialized.
     */
    public function __sleep()
    {
        throw new CannotSerializeKeyException();
    }

    /**
     * Don't allow this object to ever be unserialized.
     */
    public function __wakeup()
    {
        throw new CannotSerializeKeyException();
    }

    /**
     * Get the actual key material.
     *
     * @return string
     */
    public function getRawKeyMaterial(): string
    {
        return safe_str_cpy($this->keyMaterial);
    }
}
