<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Session\Fingerprint\ClientIpGenerator;

class ClientIpGeneratorTest extends TestCase
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
        $request->shouldReceive('hasHeader')
            ->with('HTTP_X_FORWARDED_FOR')
            ->once()
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('REMOTE_ADDR')
            ->once()
            ->andReturn(false);

        $generator = new ClientIpGenerator($request);

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, mb_strlen($generator->generate()));
    }

    public function testGenerateWithProxyIp()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')
            ->with('HTTP_X_FORWARDED_FOR')
            ->once()
            ->andReturn(true);
        $request->shouldReceive('getHeader')
            ->with('HTTP_X_FORWARDED_FOR')
            ->andReturn('111.111.111.111,123.45.67.178');

        $generator = new ClientIpGenerator($request);

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, mb_strlen($generator->generate()));
    }

    public function testGenerateWithIp()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')
            ->with('HTTP_X_FORWARDED_FOR')
            ->once()
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('REMOTE_ADDR')
            ->once()
            ->andReturn(true);
        $request->shouldReceive('getHeader')
            ->with('REMOTE_ADDR')
            ->andReturn('100.8.116.127');

        $generator = new ClientIpGenerator($request);

        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, mb_strlen($generator->generate()));

        // return empty ip string
        self::assertInternalType('string', $generator->generate());
        self::assertSame(40, mb_strlen($generator->generate()));
    }
}
