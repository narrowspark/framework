<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Session\Fingerprint\ClientIpGenerator;

/**
 * @internal
 */
final class ClientIpGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->andReturn(['REMOTE_ADDR' => '127.0.0.1']);
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

        $generator = new ClientIpGenerator($request);

        static::assertSame(40, \mb_strlen($generator->generate()));
    }
}
