<?php
namespace Viserio\Queue\Connectors;

use Exception;
use Viserio\Contracts\{
    Encryption\Encrypter as EncrypterContract,
    Queue\QueueConnector as QueueConnectorContract
};
use Viserio\Support\Traits\ContainerAwareTrait;

abstract class AbstractQueue implements QueueConnectorContract
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
     * @return  \Viserio\Contracts\Encryption\Encrypter
     *
     * @throws \Exception
     */
    protected function getEncrypter(): EncrypterContract
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
