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

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Session\Fingerprint\UserAgentGenerator;

/**
 * @internal
 *
 * @small
 */
final class UserAgentGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => 'test']);
        $generator = new UserAgentGenerator($request);

        self::assertSame(40, \strlen($generator->generate()));

        $request = \Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn([]);

        $generator = new UserAgentGenerator($request);

        self::assertSame(40, \strlen($generator->generate()));
    }
}
