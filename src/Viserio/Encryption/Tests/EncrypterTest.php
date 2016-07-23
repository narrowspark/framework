<?php

declare(strict_types=1);
namespace Viserio\Encryption\Tests;

use Defuse\Crypto\Key;
use Viserio\Encryption\Encrypter;

class EncrypterTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareEncryptedValues()
    {
        $e = new Encrypter(Key::createNewRandomKey());
        $encrypted1 = $e->encrypt('foo');
        $encrypted2 = $e->encrypt('foo');
        $encrypted3 = $e->encrypt('bar');

        $this->assertTrue($e->compare($encrypted1, $encrypted2));
        $this->assertFalse($e->compare($encrypted1, $encrypted3));
    }

    public function testEncryptionAndDecrypt()
    {
        $e = new Encrypter(Key::createNewRandomKey());

        $encrypted = $e->encrypt('foo');

        $this->assertNotEquals('foo', $encrypted);
        $this->assertEquals('foo', $e->decrypt($encrypted));
    }
}
