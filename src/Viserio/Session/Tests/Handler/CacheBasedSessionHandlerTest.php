<?php
declare(strict_types=1);
namespace Viserio\Session\Tests\Handler;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Viserio\Session\Handler\CacheBasedSessionHandler;

class CacheBasedSessionHandlerTest extends TestCase
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

        self::assertTrue($handler->open('test', 'temp'));
    }

    public function testCloseReturnsTrue()
    {
        $handler = $this->handler;

        self::assertTrue($handler->close());
    }

    public function testGcSuccessfullyReturnsTrue()
    {
        $handler = $this->handler;

        self::assertTrue($handler->gc(2));
    }
}
