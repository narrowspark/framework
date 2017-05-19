<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Http;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Support\Http\ClientIp;

class ClientIpTest extends MockeryTestCase
{
    public function testGetIpAddressByRemoteAddr()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '192.168.1.1']);
        $request->shouldReceive('hasHeader')
            ->with('Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded-For')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Cluster-Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('Client-Ip')
            ->andReturn(false);

        $clientIp = new ClientIp($request);

        static::assertSame('192.168.1.1', $clientIp->getIpAddress());
    }

    public function testGetIpIsNullIfMissing()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn([]);
        $request->shouldReceive('hasHeader')
            ->with('Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded-For')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Cluster-Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('Client-Ip')
            ->andReturn(false);

        $clientIp = new ClientIp($request);

        static::assertNull($clientIp->getIpAddress());
    }

    public function testGetIpByXForwardedFor()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '192.168.1.1']);
        $request->shouldReceive('hasHeader')
            ->with('Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded-For')
            ->andReturn(true);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Cluster-Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('X-Forwarded-For')
            ->andReturn('192.168.1.3, 192.168.1.2, 192.168.1.1');

        $clientIp = new ClientIp($request);

        static::assertSame('192.168.1.3', $clientIp->getIpAddress());
    }

    public function testGetIpByHttpClientIp()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '192.168.1.1']);
        $request->shouldReceive('hasHeader')
            ->with('Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded-For')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Cluster-Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('Client-Ip')
            ->andReturn(true);
        $request->shouldReceive('getHeaderLine')
            ->with('Client-Ip')
            ->andReturn('192.168.1.3');

        $clientIp = new ClientIp($request);

        static::assertSame('192.168.1.3', $clientIp->getIpAddress());
    }

    public function testGetIpByXForwardedForIpV6()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '192.168.1.1']);
        $request->shouldReceive('hasHeader')
            ->with('Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded-For')
            ->andReturn(true);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Cluster-Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('X-Forwarded-For')
            ->andReturn('001:DB8::21f:5bff:febf:ce22:8a2e');

        $clientIp = new ClientIp($request);

        static::assertSame('001:DB8::21f:5bff:febf:ce22:8a2e', $clientIp->getIpAddress());
    }

    public function testGetIpByForwardedWithMultipleFor()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '192.168.1.1']);
        $request->shouldReceive('hasHeader')
            ->with('Forwarded')
            ->andReturn(true);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded-For')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Cluster-Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('Forwarded')
            ->andReturn('for=192.0.2.43, for=198.51.100.17;by=203.0.113.60;proto=http;host=example.com');

        $clientIp = new ClientIp($request);

        static::assertSame('192.0.2.43', $clientIp->getIpAddress());
    }

    public function testGetIpByForwardedhWithIpV6()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '192.168.1.1']);
        $request->shouldReceive('hasHeader')
            ->with('Forwarded')
            ->andReturn(true);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded-For')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Cluster-Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('Forwarded')
            ->andReturn('For="[2001:db8:cafe::17]:4711", for=_internalProxy');

        $clientIp = new ClientIp($request);

        static::assertSame('2001:db8:cafe::17', $clientIp->getIpAddress());
    }

    public function testGetIpByXForwardedForWithInvalidIp()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => '192.168.1.1']);
        $request->shouldReceive('hasHeader')
            ->with('Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded-For')
            ->andReturn(true);
        $request->shouldReceive('hasHeader')
            ->with('X-Forwarded')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('X-Cluster-Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('hasHeader')
            ->with('Client-Ip')
            ->andReturn(false);
        $request->shouldReceive('getHeaderLine')
            ->with('X-Forwarded-For')
            ->andReturn('For');

        $clientIp = new ClientIp($request);

        static::assertSame('192.168.1.1', $clientIp->getIpAddress());
    }
}
