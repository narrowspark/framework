<?php
declare(strict_types=1);
namespace Viserio\Session\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Session\Fingerprint\ClientIpGenerator;

class ClientIpGeneratorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGenerate()
    {
        $request = $this->mock(ServerRequestInterface::class);

        $generator = new ClientIpGenerator($request);

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, strlen($generator->generate()));
    }

    public function testGenerateWithProxyIp()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '111.111.111.111,123.45.67.178';

        $request = $this->mock(ServerRequestInterface::class);

        $generator = new ClientIpGenerator($request);

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, strlen($generator->generate()));

        unset($_SERVER['HTTP_X_FORWARDED_FOR']);
    }

    public function testGenerateWithIp()
    {
        $_SERVER['REMOTE_ADDR'] = '192.0.2.60';

        $request = $this->mock(ServerRequestInterface::class);

        $generator = new ClientIpGenerator($request);

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, strlen($generator->generate()));

        unset($_SERVER['REMOTE_ADDR']);

        // return empty ip string
        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, strlen($generator->generate()));
    }
}
