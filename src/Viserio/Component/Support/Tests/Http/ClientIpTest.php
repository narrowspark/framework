<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests\Http;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Support\Http\ClientIp;

class ClientIpTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGetIpAddress()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('hasHeader')
            ->with('HTTP_X_FORWARDED_FOR')
            ->once()
            ->andReturn(true);
        $request->shouldReceive('getHeader')
            ->with('HTTP_X_FORWARDED_FOR')
            ->andReturn('111.111.111.111,123.45.67.178');

        $clientIp = new ClientIp($request);

        static::assertSame('111.111.111.111', $clientIp->getIpAddress());

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

        $clientIp = new ClientIp($request);

        static::assertSame('100.8.116.127', $clientIp->getIpAddress());
    }
}
