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

namespace Viserio\Component\Session\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Session\Fingerprint\ClientIpGenerator;

/**
 * @internal
 *
 * @small
 */
final class ClientIpGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $request = Mockery::mock(ServerRequestInterface::class);
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

        self::assertSame(40, \strlen($generator->generate()));
    }
}
