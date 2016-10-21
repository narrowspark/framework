<?php
declare(strict_types=1);
namespace Viserio\Session\Tests\Handler;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Session\Handler\CacheBasedSessionHandler;

class CacheBasedSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    /**
     * @var \Viserio\Session\Handler\CacheBasedSessionHandler
     */
    private $handler;

    public function setUp()
    {
        parent::setUp();

        $this->handler = new CacheBasedSessionHandler(
            $this->mock(CacheItemPoolInterface::class),
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

    public function testGcSuccessfullyReturnsTrue()
    {
        $handler = $this->handler;

        $this->assertTrue($handler->gc(2));
    }
}
