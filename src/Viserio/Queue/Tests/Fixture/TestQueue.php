<?php
namespace Viserio\Queue\Tests\Fixture;

use Viserio\Contracts\Encryption\Encrypter as EncrypterContract;
use Viserio\Support\Traits\ContainerAwareTrait;

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
     * @return \Viserio\Contracts\Encryption\Encrypter
     *
     * @throws \Exception
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
     * @param  \Viserio\Contracts\Encryption\Encrypter  $encrypter
     *
     * @return void
     */
    public function setEncrypter(EncrypterContract $encrypter)
    {
        $this->encrypter = $encrypter;
    }
}
