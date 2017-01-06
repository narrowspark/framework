<?php
declare(strict_types=1);
namespace Viserio\Encryption\Tests;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Encryption\Encrypter;

class EncrypterTest extends TestCase
{
    public function testCompareEncryptedValues()
    {
        $e          = new Encrypter(Key::createNewRandomKey());
        $encrypted1 = $e->encrypt('foo');
        $encrypted2 = $e->encrypt('foo');
        $encrypted3 = $e->encrypt('bar');

        self::assertTrue($e->compare($encrypted1, $encrypted2));
        self::assertFalse($e->compare($encrypted1, $encrypted3));
    }

    public function testEncryptionAndDecrypt()
    {
        $e = new Encrypter(Key::createNewRandomKey());

        $encrypted = $e->encrypt('foo');

        self::assertNotEquals('foo', $encrypted);
        self::assertEquals('foo', $e->decrypt($encrypted));
    }
}
