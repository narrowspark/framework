<?php
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Viserio\Session\Fingerprint\ClientIpGenerator;

class ClientIpGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $key = Key::createNewRandomKey();
        $generator = new ClientIpGenerator($key->saveToAsciiSafeString(), 'test');

        $this->assertInternalType('string', $generator->generate());
        $this->assertSame(40, strlen($generator->generate()));
    }
}
