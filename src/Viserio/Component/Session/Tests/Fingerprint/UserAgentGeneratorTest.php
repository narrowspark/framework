<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Session\Fingerprint\UserAgentGenerator;

class UserAgentGeneratorTest extends TestCase
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
