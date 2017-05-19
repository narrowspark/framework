<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use Cake\Chronos\Chronos;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Session\Handler\CookieSessionHandler;

class CookieSessionHandlerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Session\Handler\CookieSessionHandler
     */
    private $handler;

    public function setUp()
    {
        parent::setUp();

        $this->handler = new CookieSessionHandler(
            $this->mock(JarContract::class),
            300
        );
    }

    public function testOpenReturnsTrue()
    {
        $handler = $this->handler;

        self::assertTrue($handler->open('test', 'temp'));
    }

    public function testCloseReturnsTrue()
    {
        $handler = $this->handler;

        self::assertTrue($handler->close());
    }

    public function testReadExistingSessionReturnsTheData()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request
            ->shouldReceive('getCookieParams')
            ->once()
            ->andReturn('{
                "temp": {
                    "expires": "' . Chronos::now()->addSeconds(350)->getTimestamp() . '",
                    "data": "Foo Bar"
                }
            }');
        $handler = $this->handler;
        $handler->setRequest($request);

        self::assertSame('Foo Bar', $handler->read('temp'));
    }

    public function testReadMissingSessionReturnsAnEmptyString()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request
            ->shouldReceive('getCookieParams')
            ->once()
            ->andReturn('');
        $handler = $this->handler;
        $handler->setRequest($request);

        self::assertSame('', $handler->read('12'));
    }

    public function testWriteSuccessfullyReturnsTrue()
    {
        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once()
            ->with(
                'write.sess',
                json_encode(
                    [
                        'data'    => ['user_id' => 1],
                        'expires' => Chronos::now()->addSeconds(300)->getTimestamp(),
                    ],
                    \JSON_PRESERVE_ZERO_FRACTION
                ),
                300
            );
        $handler = new CookieSessionHandler(
            $jar,
            300
        );

        self::assertTrue($handler->write('write.sess', ['user_id' => 1]));
    }

    public function testGcSuccessfullyReturnsTrue()
    {
        $handler = $this->handler;

        self::assertTrue($handler->gc(2));
    }

    public function testDestroySuccessfullReturnsTrue()
    {
        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once();
        $jar->shouldReceive('delete')
            ->once()
            ->with('cookie.sess');
        $jar->shouldReceive('hasQueued')
            ->once()
            ->andReturn(true);

        $handler = new CookieSessionHandler(
            $jar,
            300
        );

        self::assertTrue($handler->destroy('cookie.sess'));
    }
}
