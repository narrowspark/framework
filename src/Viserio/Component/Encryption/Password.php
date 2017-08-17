<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\Password as PasswordContract;
use Viserio\Component\Contracts\Encryption\HiddenString as HiddenStringContract;

final class Password implements PasswordContract
{
    /**
     * @var \Viserio\Component\Encryption\Key
     */
    private $key;

    public function __construct(Key $key)
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function hash(HiddenStringContract $password, string $level = KeyFactory::INTERACTIVE): string
    {
    }

    /**
     * {@inheritdoc}
     */
    public function verify(HiddenStringContract $password, string $hashedValue): bool
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
