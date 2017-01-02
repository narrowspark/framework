<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Viserio\Session\Fingerprint\UserAgentGenerator;
use PHPUnit\Framework\TestCase;

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
