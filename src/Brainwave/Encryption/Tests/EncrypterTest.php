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
        $e = $this->getEncrypter();
        $this->assertNotEquals('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', $e->encrypt('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb'));
        $encrypted = $e->encrypt('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');
        $this->assertEquals('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', $e->decrypt($encrypted));
    }

    public function testEncryptionWithCustomCipher()
    {
        $e = new Encrypter(str_repeat('a', 32), 'AES-128-CBC');
        $this->assertNotEquals('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', $e->encrypt('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb'));
        $encrypted = $e->encrypt('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');
        $this->assertEquals('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', $e->decrypt($encrypted));
    }

    /**
     * @expectedException Brainwave\Contracts\Encryption\DecryptException
     */
    public function testExceptionThrownWhenPayloadIsInvalid()
    {
        $e = $this->getEncrypter();
        $payload = $e->encrypt('foo');
        $payload = str_shuffle($payload);
        $e->decrypt($payload);
    }

    protected function getEncrypter()
    {
        return new Encrypter(str_repeat('a', 32));
    }
}
