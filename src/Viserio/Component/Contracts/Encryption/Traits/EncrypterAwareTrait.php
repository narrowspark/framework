<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Encryption\Traits;

use RuntimeException;
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

    /**
     * Get the encrypter instance.
     *
     * @throws \RuntimeException
     *
     * @return \Viserio\Component\Contracts\Encryption\Encrypter
     */
    public function getEncrypter(): EncrypterContract
    {
        if (! $this->encrypter) {
            throw new RuntimeException('Encrypter is not set up.');
        }

        return $this->encrypter;
    }
}
