<?php
declare(strict_types=1);
namespace Viserio\Queue\Tests\Fixture;

use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;

class TestQueue
{
    use ContainerAwareTrait;

    /**
     * The encrypter implementation.
     *
     * @var \Viserio\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Get the encrypter implementation.
     *
     * @throws \Exception
     *
     * @return \Viserio\Contracts\Encryption\Encrypter
     */
    public function getEncrypter(): EncrypterContract
    {
        if ($this->encrypter === null) {
            throw new Exception('No encrypter has been set on the Queue.');
        }

        return $this->encrypter;
    }

    /**
     * Set the encrypter implementation.
     *
     * @param \Viserio\Contracts\Encryption\Encrypter $encrypter
     */
    public function setEncrypter(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }
}
