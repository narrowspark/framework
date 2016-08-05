<?php
declare(strict_types=1);
namespace Viserio\Contracts\Encryption\Traits;

use RuntimeException;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;

trait EncrypterAwareTrait
{
    /**
     * Encrypter instance.
     *
     * @var \Viserio\Contracts\Encryption\Encrypter|null
     */
    protected $encrypter;

    /**
     * Set a encrypter instance.
     *
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
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
     * @return \Viserio\Contracts\Encryption\Encrypter
     */
    public function getEncrypter(): EncrypterContract
    {
        if (! $this->encrypter) {
            throw new RuntimeException('Encrypter is not set up.');
        }

        return $this->encrypter;
    }
}
