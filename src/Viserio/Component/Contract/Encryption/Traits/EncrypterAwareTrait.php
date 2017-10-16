<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Encryption\Traits;

use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;

trait EncrypterAwareTrait
{
    /**
     * Encrypter instance.
     *
     * @var null|\Viserio\Component\Contract\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Set a encrypter instance.
     *
     * @param \Viserio\Component\Contract\Encryption\Encrypter $encrypter
     *
     * @return $this
     */
    public function setEncrypter(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;

        return $this;
    }
}
