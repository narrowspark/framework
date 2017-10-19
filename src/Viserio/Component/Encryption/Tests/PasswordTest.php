<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Encryption\Encrypter;
use Viserio\Component\Encryption\HiddenString;
use Viserio\Component\Encryption\Key;
use Viserio\Component\Encryption\Password;

/**
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */
class PasswordTest extends TestCase
{
    /**
     * @var \Viserio\Component\Encryption\Encrypter
     */
    private $encrypter;

    protected function setUp(): void
    {
        parent::setUp();

        $key = new Key(new HiddenString(\str_repeat('A', 32)));

        $this->encrypter = new Encrypter($key);
    }

    public function testPasswordEncryption(): void
    {
        $password = new Password($this->encrypter);
        $hash     = $password->hash(new HiddenString('test password'));

        self::assertTrue(\is_string($hash));
        self::assertTrue($password->verify(new HiddenString('test password'), $hash));
        self::assertFalse($password->verify(new HiddenString('wrong password'), $hash));
    }
}
