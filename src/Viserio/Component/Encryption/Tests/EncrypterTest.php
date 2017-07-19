<?php
declare(strict_types=1);
namespace Viserio\Component\Encryption\Tests;

use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Encryption\Encrypter;

class EncrypterTest extends TestCase
{
    public function testCompareEncryptedValues(): void
    {
        $e          = new Encrypter(Key::createNewRandomKey()->saveToAsciiSafeString());
        $encrypted1 = $e->encrypt('foo');
        $encrypted2 = $e->encrypt('foo');
        $encrypted3 = $e->encrypt('bar');

        self::assertTrue($e->compare($encrypted1, $encrypted2));
        self::assertFalse($e->compare($encrypted1, $encrypted3));
    }

    public function testEncryptionAndDecrypt(): void
    {
        $e = new Encrypter(Key::createNewRandomKey()->saveToAsciiSafeString());

        $encrypted = $e->encrypt('foo');

        self::assertNotEquals('foo', $encrypted);
        self::assertEquals('foo', $e->decrypt($encrypted));
    }
}
