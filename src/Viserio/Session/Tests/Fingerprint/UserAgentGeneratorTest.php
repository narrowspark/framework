<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Viserio\Session\Fingerprint\UserAgentGenerator;

class UserAgentGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $generator = new UserAgentGenerator('test');

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, mb_strlen($generator->generate()));

        $generator = new UserAgentGenerator();

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, mb_strlen($generator->generate()));
    }
}
