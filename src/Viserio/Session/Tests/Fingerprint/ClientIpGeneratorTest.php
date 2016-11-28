<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Viserio\Session\Fingerprint\ClientIpGenerator;

class ClientIpGeneratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGenerate()
    {
        $generator = new ClientIpGenerator();

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, strlen($generator->generate()));
    }

    public function testGenerateWithProxyIp()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '111.111.111.111,123.45.67.178';

        $generator = new ClientIpGenerator();

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, strlen($generator->generate()));

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function testGenerateWithIp()
    {
        $_SERVER['REMOTE_ADDR'] = '192.0.2.60';

        $generator = new ClientIpGenerator();

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, strlen($generator->generate()));

        unset($_SERVER['REMOTE_ADDR']);

        // return empty ip string
        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, strlen($generator->generate()));
    }
}
