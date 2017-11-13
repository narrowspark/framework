<?php
declare(strict_types=1);
namespace Viserio\Component\Session\Tests\Handler;

use Cake\Chronos\Chronos;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Cookie\QueueingFactory as JarContract;
use Viserio\Component\Cookie\AbstractCookie;
use Viserio\Component\Session\Handler\CookieSessionHandler;

class CookieSessionHandlerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Session\Handler\CookieSessionHandler
     */
    private $handler;

    public function setUp(): void
    {
        parent::setUp();

        $this->handler = new CookieSessionHandler(
            $this->mock(JarContract::class),
            300
        );
    }

    public function testOpenReturnsTrue(): void
    {
        self::assertTrue($this->handler->open('test', 'temp'));
    }

    public function testCloseReturnsTrue(): void
    {
        self::assertTrue($this->handler->close());
    }

    public function testReadExistingSessionReturnsTheData(): void
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request
            ->shouldReceive('getCookieParams')
            ->once()
            ->andReturn(['temp' => \base64_encode(\json_encode(
                [
                    'expires' => Chronos::now()->addSeconds(350)->getTimestamp(),
                    'data'    => 'Foo Bar',
                ],
                    \JSON_PRESERVE_ZERO_FRACTION
            ))]);
        $this->handler->setRequest($request);

        self::assertSame('Foo Bar', $this->handler->read('temp'));
    }

    public function testReadMissingSessionReturnsAnEmptyString(): void
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request
            ->shouldReceive('getCookieParams')
            ->once()
            ->andReturn(['']);
        $handler = $this->handler;
        $handler->setRequest($request);

        self::assertSame('', $handler->read('12'));
    }

    public function testWriteSuccessfullyReturnsTrue(): void
    {
        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('queue')
            ->once()
            ->with(
                'write.sess',
                \base64_encode(\json_encode(
                    [
                        'data'    => ['user_id' => 1],
                        'expires' => Chronos::now()->addSeconds(300)->getTimestamp(),
                    ],
                    \JSON_PRESERVE_ZERO_FRACTION
                )),
                300
            );
        $handler = new CookieSessionHandler(
            $jar,
            300
        );

        self::assertTrue($handler->write('write.sess', ['user_id' => 1]));
    }

    public function testGcSuccessfullyReturnsTrue(): void
    {
        $handler = $this->handler;

        self::assertTrue($handler->gc(2));
    }

    public function testDestroySuccessfullReturnsTrue(): void
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

    public function testUpdateTimestamp(): void
    {
        $cookie = $this->mock(AbstractCookie::class);
        $cookie->shouldReceive('withExpires')
            ->once()
            ->with(\Mockery::type('int'));

        $jar = $this->mock(JarContract::class);
        $jar->shouldReceive('getQueuedCookies')
            ->once()
            ->andReturn([
                'cookie.sess' => $cookie,
            ]);
        $jar->shouldReceive('queue')
            ->twice();
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

        self::assertTrue($handler->updateTimestamp('cookie.sess', 'foo'));
    }
}
