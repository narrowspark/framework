<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption\Traits;

use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;

trait EncrypterAwareTrait
{
    /**
     * Encrypter instance.
     *
     * @var null|\Viserio\Component\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Set a encrypter instance.
     *
     * @param \Viserio\Component\Contracts\Encryption\Encrypter $encrypter
     *
     * @return $this
     */
    public function setEncrypter(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;

        return $this;
    }
}
