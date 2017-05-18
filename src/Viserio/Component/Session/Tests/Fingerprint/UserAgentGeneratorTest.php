<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Session\Fingerprint\UserAgentGenerator;
use Psr\Http\Message\ServerRequestInterface;

class UserAgentGeneratorTest extends MockeryTestCase
{
    public function testGenerate()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn(['REMOTE_ADDR' => 'test']);
        $generator = new UserAgentGenerator($request);

        self::assertSame(40, mb_strlen($generator->generate()));

        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->once()
            ->andReturn([]);

        $generator = new UserAgentGenerator($request);

        self::assertSame(40, mb_strlen($generator->generate()));
    }
}
