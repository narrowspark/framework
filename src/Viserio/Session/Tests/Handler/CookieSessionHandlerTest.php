<?php
declare(strict_types=1);
namespace Viserio\Session\Tests\Handler;

use Cake\Chronos\Chronos;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Cookie\QueueingFactory as JarContract;
use Viserio\Session\Handler\CookieSessionHandler;

class CookieSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var \Viserio\Session\Handler\CookieSessionHandler
     */
    private $handler;

    public function setUp()
    {
        parent::setUp();

        $this->handler = new CookieSessionHandler(
            $this->mock(JarContract::class),
            5
        );
    }

    public function testOpenReturnsTrue()
    {
        $handler = $this->handler;

        $this->assertTrue($handler->open('test', 'temp'));
    }

    public function testCloseReturnsTrue()
    {
        $handler = $this->handler;

        $this->assertTrue($handler->close());
    }

    public function testReadExistingSessionReturnsTheData()
    {
        $request = $this->mock(ServerRequestInterface::class);
        $request
            ->shouldReceive('getCookieParams')
            ->once()
            ->andReturn('{
                "temp": {
                    "expires": "' . Chronos::now()->addMinutes(6)->getTimestamp() . '",
                    "data": "Foo Bar"
                }
            }');
        $handler = $this->handler;
        $handler->setRequest($request);

        $this->assertSame('Foo Bar', $handler->read('temp'));
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

        $this->assertSame('', $handler->read('12'));
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
                        'data' => ['user_id' => 1],
                        'expires' => Chronos::now()->addMinutes(5)->getTimestamp(),
                    ],
                    \JSON_PRESERVE_ZERO_FRACTION
                ),
                5
            );
        $handler = new CookieSessionHandler(
            $jar,
            5
        );

        $this->assertTrue($handler->write('write.sess', ['user_id' => 1]));
    }

    public function testGcSuccessfullyReturnsTrue()
    {
        $handler = $this->handler;

        $this->assertTrue($handler->gc(2));
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
            5
        );

        $this->assertTrue($handler->destroy('cookie.sess'));
    }
}
