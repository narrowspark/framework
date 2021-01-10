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
use Viserio\Component\Session\Fingerprint\UserAgentGenerator;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class UserAgentGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => 'test']);
        $generator = new UserAgentGenerator($request);

        self::assertSame(40, \strlen($generator->generate()));

        $request = Mockery::mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn([]);

        $generator = new UserAgentGenerator($request);

        self::assertSame(40, \strlen($generator->generate()));
    }
}
