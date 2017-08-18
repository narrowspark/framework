<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\Exception\CannotCloneKey;
use Viserio\Component\Contracts\Encryption\Exception\CannotSerializeKey;

final class Key
{
    /**
     * @var string
     */
    private $keyMaterial;

    /**
     * Don't let this ever succeed
     *
     * @throws \Viserio\Component\Contracts\Encryption\Exception\CannotCloneKey
     */
    public function __clone()
    {
        throw new CannotCloneKey();
    }

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
     * Make sure you wipe the key from memory on destruction
     */
    public function __destruct()
    {
        \sodium_memzero($this->keyMaterial);

        $this->keyMaterial = null;
    }

    /**
     * Don't allow this object to ever be serialized.
     */
    public function __sleep()
    {
        throw new CannotSerializeKey;
    }

    /**
     * Don't allow this object to ever be unserialized.
     */
    public function __wakeup()
    {
        throw new CannotSerializeKey;
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
