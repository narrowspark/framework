<?php
declare(strict_types=1);
namespace Viserio\Component\Session;

use SessionHandlerInterface as SessionHandlerContract;
use Viserio\Component\Contract\Encryption\Encrypter as EncrypterContract;
use Viserio\Component\Contract\Encryption\Traits\EncrypterAwareTrait;
use Viserio\Component\Encryption\HiddenString;

class EncryptedStore extends Store
{
    use EncrypterAwareTrait;

    /**
     * Create a new session instance.
     *
     * @param string                                           $name
     * @param \SessionHandlerInterface                         $handler
     * @param \Viserio\Component\Contract\Encryption\Encrypter $encrypter
     */
    public function __construct(string $name, SessionHandlerContract $handler, EncrypterContract $encrypter)
    {
        parent::__construct($name, $handler);

        $this->encrypter = $encrypter;
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareForReadFromHandler($data): array
    {
        $hiddenString = $this->encrypter->decrypt($data);

        if ($decryptedValue = $hiddenString->getString()) {
            $sessionData = \json_decode($decryptedValue, true);

            \sodium_memzero($decryptedValue);

            if ($sessionData === null) {
                return [];
            }

            return $sessionData;
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareForWriteToHandler(string $data): string
    {
        return $this->encrypter->encrypt(new HiddenString($data));
    }
}
