<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\Exception\CannotCloneKeyException;
use Viserio\Component\Contracts\Encryption\Exception\CannotSerializeKeyException;

final class Key
{
    /**
     * @var string
     */
    private $keyMaterial;

    /**
     * You probably should not be using this directly.
     *
     * @param HiddenString $keyMaterial - The actual key data
     */
    public function __construct(HiddenString $keyMaterial)
    {
        $this->keyMaterial = str_cpy($keyMaterial->getString());
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
        return str_cpy($this->keyMaterial);
    }
}
