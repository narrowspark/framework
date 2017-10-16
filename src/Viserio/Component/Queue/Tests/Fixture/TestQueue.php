<?php
declare(strict_types=1);
namespace Viserio\Component\Queue\Tests\Fixture;

use Exception;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;

class TestQueue
{
    use ContainerAwareTrait;

    /**
     * The encrypter implementation.
     *
     * @var \Viserio\Component\Contract\Encryption\Encrypter
     */
    protected $encrypter;

    /**
     * Get the encrypter implementation.
     *
     * @throws \Exception
     *
     * @return \Viserio\Component\Contract\Encryption\Encrypter
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
     * @param \Viserio\Component\Contract\Encryption\Encrypter $encrypter
     */
    public function setEncrypter(EncrypterContract $encrypter): void
    {
        $this->encrypter = $encrypter;
    }
}
