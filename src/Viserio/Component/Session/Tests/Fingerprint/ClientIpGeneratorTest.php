<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Session\Fingerprint\ClientIpGenerator;

class ClientIpGeneratorTest extends MockeryTestCase
{
    public function testGenerate()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getServerParams')
            ->andReturn(['REMOTE_ADDR' => '127.0.0.1']);

        $generator = new ClientIpGenerator($request);

        self::assertSame(40, mb_strlen($generator->generate()));
    }
}
