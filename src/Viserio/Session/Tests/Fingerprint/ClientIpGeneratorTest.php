<?php

declare(strict_types=1);
namespace Viserio\Session\Tests;

use Defuse\Crypto\Key;
use Viserio\Session\Fingerprint\ClientIpGenerator;

class ClientIpGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $generator = new ClientIpGenerator(Key::createNewRandomKey());

        $this->assertInternalType('string', $generator->generate());
        $this->assertSame(40, strlen($generator->generate()));
    }
}
