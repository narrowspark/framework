<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\Password as PasswordContract;

final class Password implements PasswordContract
{
    /**
     * {@inheritdoc}
     */
    public static function hash(string $password): string
    {
    }

    /**
     * {@inheritdoc}
     */
    public function verify(string $password, string $hashedValue): bool
    {
    }

    /**
     * @param string $hashedValue
     * @param string $newKey
     *
     * @return string
     */
    public function shouldRecreate(string $hashedValue, string $newKey): string
    {
    }
}
