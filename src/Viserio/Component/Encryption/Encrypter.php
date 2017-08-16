<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\Encrypter as EncrypterContract;

final class Encrypter implements EncrypterContract
{
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
