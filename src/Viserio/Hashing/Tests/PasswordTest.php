<?php
namespace Viserio\Hashing\Tests;

use Defuse\Crypto\Key;
use Viserio\Hashing\Password;

class PasswordTest extends \PHPUnit_Framework_TestCase
{
    private $password;

    public function setUp()
    {
        $this->password = new Password(Key::createNewRandomKey());
    }

    public function testCreate()
    {
        $hash = $this->password->create('totally-insecure-but-lengthy-password');

        $this->assertEquals(288, strlen($hash));
    }

    public function testVerify()
    {
        $password = 'totally-insecure-but-lengthy-password';
        $otherPassword = 'totally-awesome-password';

        $hash = $this->password->create($password);

        $this->assertEquals(false, $this->password->verify($otherPassword, $hash));
        $this->assertEquals(true, $this->password->verify($password, $hash));
    }

    public function testShouldRecreate()
    {
        $key = Key::createNewRandomKey();
        $hash = $this->password->create('totally-insecure-but-lengthy-password');

        $this->assertNotSame($hash, $this->password->shouldRecreate($hash, $key));
    }
}
