<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Viserio\Session\Fingerprint\UserAgentGenerator;

class UserAgentGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $generator = new UserAgentGenerator(Key::createNewRandomKey(), 'test');

        $this->assertInternalType('string', $generator->generate());
        $this->assertSame(40, strlen($generator->generate()));

        $generator = new UserAgentGenerator(Key::createNewRandomKey());

        $this->assertInternalType('string', $generator->generate());
        $this->assertSame(40, strlen($generator->generate()));
    }
}
