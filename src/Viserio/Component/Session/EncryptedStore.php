<?php
declare(strict_types=1);
namespace Viserio\Component\Session;

use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\Symmetric\Crypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;
use SessionHandlerInterface as SessionHandlerContract;

class EncryptedStore extends Store
{
    /**
     * Encryption key instance.
     *
     * @var \ParagonIE\Halite\Symmetric\EncryptionKey
     */
    private $key;

    /**
     * Create a new session instance.
     *
     * @param string                                    $name
     * @param \SessionHandlerInterface                  $handler
     * @param \ParagonIE\Halite\Symmetric\EncryptionKey $key
     */
    public function __construct(string $name, SessionHandlerContract $handler, EncryptionKey $key)
    {
        parent::__construct($name, $handler);

        $this->key = $key;
    }

    /**
     * Hide this from var_dump(), etc.
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'key' => 'private',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareForReadFromHandler($data): array
    {
        $hiddenString = Crypto::decrypt($data, $this->key);

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
        return Crypto::encrypt(new HiddenString($data), $this->key);
    }
}
