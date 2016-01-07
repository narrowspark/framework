<?php
namespace Viserio\Encryption\Test;

use Mockery as Mock;
use Viserio\Encryption\Encrypter;

class EncrypterTest extends \PHPUnit_Framework_TestCase
{
    public function testCompareEncryptedValues()
    {
        $e = new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16));
        $encrypted1 = $e->encrypt('foo');
        $encrypted2 = $e->encrypt('foo');
        $encrypted3 = $e->encrypt('bar');

        $this->assertTrue($e->compare($encrypted1, $encrypted2));
        $this->assertTrue($e->compare($encrypted1, 'foo'));
        $this->assertFalse($e->compare($encrypted1, $encrypted3));
    }

    public function testEncryption()
    {
        $e = new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16));
        $encrypted = $e->encrypt('foo');
        $this->assertNotEquals('foo', $encrypted);
        $this->assertEquals('foo', $e->decrypt($encrypted));
    }

    public function testWithCustomCipher()
    {
        $e = new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('b', 32), 'AES-256', 'CBC');
        $encrypted = $e->encrypt('bar');
        $this->assertNotEquals('bar', $encrypted);
        $this->assertEquals('bar', $e->decrypt($encrypted));
    }

    public function testAllowLongerKeyForBC()
    {
        $e = new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('z', 32));
        $encrypted = $e->encrypt('baz');
        $this->assertNotEquals('baz', $encrypted);
        $this->assertEquals('baz', $e->decrypt($encrypted));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The only supported ciphers are [AES-128-CBC, AES-256-CBC] with the correct key lengths.
     */
    public function testWithBadKeyLength()
    {
        new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 5));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The only supported ciphers are [AES-128-CBC, AES-256-CBC] with the correct key lengths.
     */
    public function testWithBadKeyLengthAlternativeCipher()
    {
        new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16), 'AES-256', 'CFB8');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The only supported ciphers are [AES-128-CBC, AES-256-CBC] with the correct key lengths.
     */
    public function testWithUnsupportedCipher()
    {
        $e = new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('c', 16), 'AES-256', 'CFB8');
    }

    /**
     * @expectedException Viserio\Contracts\Encryption\DecryptException
     * @expectedExceptionMessage The payload is invalid.
     */
    public function testExceptionThrownWhenPayloadIsInvalid()
    {
        $e = new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16));
        $payload = $e->encrypt('foo');
        $payload = str_shuffle($payload);
        $e->decrypt($payload);
    }

    /**
     * @expectedException Viserio\Contracts\Encryption\DecryptException
     * @expectedExceptionMessage The MAC is invalid.
     */
    public function testExceptionThrownWithDifferentKey()
    {
        $a = new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16));
        $b = new Encrypter(Mock::mock('Viserio\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('b', 16));
        $b->decrypt($a->encrypt('baz'));
    }
}
