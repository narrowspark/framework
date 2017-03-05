<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Fixture;

use Exception;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;

class TestQueue
{
    use ContainerAwareTrait;

    /**
     * The encrypter implementation.
     *
     * @var \Viserio\Component\Contracts\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Get the encrypter implementation.
     *
     * @throws \Exception
     *
     * @return \Viserio\Component\Contracts\Encryption\Encrypter
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
     * @param \Viserio\Component\Contracts\Encryption\Encrypter $encrypter
     */
    public function setEncrypter(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }
}
