<?php
declare(strict_types=1);
namespace Viserio\Component\Hashing\Tests;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Hashing\Password;

class PasswordTest extends TestCase
{
    private $password;

    public function setUp()
    {
        $this->password = new Password(Key::createNewRandomKey()->saveToAsciiSafeString());
    }

    public function testCreate()
    {
        $hash = $this->password->create('totally-insecure-but-lengthy-password');

        self::assertEquals(288, mb_strlen($hash));
    }

    public function testVerify()
    {
        $password      = 'totally-insecure-but-lengthy-password';
        $otherPassword = 'totally-awesome-password';

        $hash = $this->password->create($password);

        self::assertEquals(false, $this->password->verify($otherPassword, $hash));
        self::assertEquals(true, $this->password->verify($password, $hash));
    }

    public function testShouldRecreate()
    {
        $hash = $this->password->create('totally-insecure-but-lengthy-password');

        self::assertNotSame($hash, $this->password->shouldRecreate($hash, Key::createNewRandomKey()->saveToAsciiSafeString()));
    }
}
