<?php
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Viserio\Session\Fingerprint\UserAgentGenerator;

class UserAgentGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $key = Key::createNewRandomKey();
        $generator = new UserAgentGenerator($key->saveToAsciiSafeString(), 'test');

        $this->assertInternalType('string', $generator->generate());
        $this->assertSame(40, strlen($generator->generate()));

        $generator = new UserAgentGenerator($key->saveToAsciiSafeString());

        $this->assertInternalType('string', $generator->generate());
        $this->assertSame(40, strlen($generator->generate()));
    }
}
