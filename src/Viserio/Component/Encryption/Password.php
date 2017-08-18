<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption;

use Viserio\Component\Contracts\Encryption\HiddenString as HiddenStringContract;
use Viserio\Component\Contracts\Encryption\Password as PasswordContract;
use Viserio\Component\Contracts\Encryption\Security as SecurityContract;

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
    public function hash(HiddenStringContract $password, string $level = SecurityContract::INTERACTIVE): string
    {
    }


    /**
     * {@inheritdoc}
     */
    public function verify(HiddenStringContract $password, string $stored): bool
    {
    }


    /**
     * {@inheritdoc}
     */
    public function needsRehash(string $stored, string $level = SecurityContract::INTERACTIVE): bool
    {
    }
}
