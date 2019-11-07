<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Support\Tests\Http;

use Mockery;
use Mockery\MockInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Support\Http\ClientIp;

/**
 * @internal
 *
 * @small
 */
final class ClientIpTest extends MockeryTestCase
{
    public function testGetIpAddressByRemoteAddr(): void
    {
        $request = Mockery::mock(ServerRequestInterface::class);
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

        self::assertSame('192.168.1.1', $clientIp->getIpAddress());
    }

    public function testGetIpIsNullIfMissing(): void
    {
        $request = Mockery::mock(ServerRequestInterface::class);
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

        self::assertNull($clientIp->getIpAddress());
    }

    public function testGetIpByXForwardedFor(): void
    {
        $request = $this->arrangeRequestWithXForwardedForHeader();
        $request->shouldReceive('getHeaderLine')
            ->with('X-Forwarded-For')
            ->andReturn('192.168.1.3, 192.168.1.2, 192.168.1.1');

        $clientIp = new ClientIp($request);

        self::assertSame('192.168.1.3', $clientIp->getIpAddress());
    }

    public function testGetIpByHttpClientIp(): void
    {
        $request = Mockery::mock(ServerRequestInterface::class);
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

        self::assertSame('192.168.1.3', $clientIp->getIpAddress());
    }

    public function testGetIpByXForwardedForIpV6(): void
    {
        $request = $this->arrangeRequestWithXForwardedForHeader();
        $request->shouldReceive('getHeaderLine')
            ->with('X-Forwarded-For')
            ->andReturn('001:DB8::21f:5bff:febf:ce22:8a2e');

        $clientIp = new ClientIp($request);

        self::assertSame('001:DB8::21f:5bff:febf:ce22:8a2e', $clientIp->getIpAddress());
    }

    public function testGetIpByForwardedWithMultipleFor(): void
    {
        $request = $this->arrangeRequestWithForwardedHeader();
        $request->shouldReceive('getHeaderLine')
            ->with('Forwarded')
            ->andReturn('for=192.0.2.43, for=198.51.100.17;by=203.0.113.60;proto=http;host=example.com');

        $clientIp = new ClientIp($request);

        self::assertSame('192.0.2.43', $clientIp->getIpAddress());
    }

    public function testGetIpByForwardedhWithIpV6(): void
    {
        $request = $this->arrangeRequestWithForwardedHeader();
        $request->shouldReceive('getHeaderLine')
            ->with('Forwarded')
            ->andReturn('For="[2001:db8:cafe::17]:4711", for=_internalProxy');

        $clientIp = new ClientIp($request);

        self::assertSame('2001:db8:cafe::17', $clientIp->getIpAddress());
    }

    public function testGetIpByXForwardedForWithInvalidIp(): void
    {
        $request = $this->arrangeRequestWithXForwardedForHeader();
        $request->shouldReceive('getHeaderLine')
            ->with('X-Forwarded-For')
            ->andReturn('For');

        $clientIp = new ClientIp($request);

        self::assertSame('192.168.1.1', $clientIp->getIpAddress());
    }

    /**
     * @return MockInterface
     */
    private function arrangeRequestWithXForwardedForHeader(): MockInterface
    {
        $request = Mockery::mock(ServerRequestInterface::class);
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

        return $request;
    }

    /**
     * @return MockInterface
     */
    private function arrangeRequestWithForwardedHeader(): MockInterface
    {
        $request = Mockery::mock(ServerRequestInterface::class);
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

        return $request;
    }
}
