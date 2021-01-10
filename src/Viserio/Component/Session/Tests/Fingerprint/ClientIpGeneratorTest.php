<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
 * @coversNothing
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
