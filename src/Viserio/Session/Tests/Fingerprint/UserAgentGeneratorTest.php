<?php
namespace Viserio\Session\Tests;

use Viserio\Session\Fingerprint\UserAgentGenerator;

class UserAgentGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $generator = new UserAgentGenerator('test');

        $this->assertInternalType('string', $generator->generate());
        $this->assertSame(40, strlen($generator->generate()));

        $generator = new UserAgentGenerator();

        $this->assertInternalType('string', $generator->generate());
        $this->assertSame(40, strlen($generator->generate()));
    }
}
