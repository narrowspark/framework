<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Defuse\Crypto\Key;
use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;

class Encrypter implements EncrypterContract
{
    /**
     * Encryption key.
     *
     * @var \Defuse\Crypto\Key
     */
    protected $key;

    /**
     * Create a new Encrypter instance.
     *
     * @param string $key
     */
    public function __construct(string $key)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function encrypt(string $plaintext): string
    {
    }

    /**
     * {@inheritdoc}
     */
    public function decrypt(string $ciphertext): string
    {
    }
}
