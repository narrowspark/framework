<?php

namespace Brainwave\Encryption\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Encryption\Encrypter;
use Mockery as Mock;

/**
 * EncrypterTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class EncrypterTest extends \PHPUnit_Framework_TestCase
{
    public function testEncryption()
    {
        $e = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16));
        $encrypted = $e->encrypt('foo');
        $this->assertNotEquals('foo', $encrypted);
        $this->assertEquals('foo', $e->decrypt($encrypted));
    }

    public function testWithCustomCipher()
    {
        $e = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('b', 32), 'AES-256', 'CBC');
        $encrypted = $e->encrypt('bar');
        $this->assertNotEquals('bar', $encrypted);
        $this->assertEquals('bar', $e->decrypt($encrypted));
    }

    public function testAllowLongerKeyForBC()
    {
        $e = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('z', 32));
        $encrypted = $e->encrypt('baz');
        $this->assertNotEquals('baz', $encrypted);
        $this->assertEquals('baz', $e->decrypt($encrypted));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.
     */
    public function testWithBadKeyLength()
    {
        $e = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 5));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.
     */
    public function testWithBadKeyLengthAlternativeCipher()
    {
        $e = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16), 'AES-256', 'CFB8');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage The only supported ciphers are AES-128-CBC and AES-256-CBC with the correct key lengths.
     */
    public function testWithUnsupportedCipher()
    {
        $e = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('c', 16), 'AES-256', 'CFB8');
    }

    /**
     * @expectedException Illuminate\Contracts\Encryption\DecryptException
     * @expectedExceptionMessage The payload is invalid.
     */
    public function testExceptionThrownWhenPayloadIsInvalid()
    {
        $e = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16));
        $payload = $e->encrypt('foo');
        $payload = str_shuffle($payload);
        $e->decrypt($payload);
    }

    /**
     * @expectedException Illuminate\Contracts\Encryption\DecryptException
     * @expectedExceptionMessage The MAC is invalid.
     */
    public function testExceptionThrownWithDifferentKey()
    {
        $a = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('a', 16));
        $b = new Encrypter(Mock::mock('Brainwave\Contracts\Hashing\Generator'), Mock::mock('RandomLib\Generator'), str_repeat('b', 16));
        $b->decrypt($a->encrypt('baz'));
    }
}
